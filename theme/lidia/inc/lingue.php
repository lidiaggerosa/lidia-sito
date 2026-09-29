<?php
/**
 * Italiano e inglese, con Polylang.
 *
 * - hreflang: `it` e `en`, mai `en-GB` (la versione inglese non è per il solo Regno Unito),
 *   `x-default` sull'italiano (docs/06-seo-technical.md §3). Solo per le pagine con una
 *   traduzione pubblicata: una pagina senza EN, o con l'EN in bozza, non ha hreflang.
 * - Testata e footer: sulle pagine inglesi il tema usa `header-en` e `footer-en`, se
 *   esistono in parts/. I template restano uno solo per le due lingue.
 * - Selettore di lingua (25/09/2026): blocco `lidia/lingua` nella testata. Porta alla stessa
 *   pagina nell'altra lingua e compare solo se quella traduzione è pubblicata. Un link, niente
 *   JavaScript: dipende solo dall'URL, quindi convive con la cache di pagina.
 *
 * Senza Polylang attivo questo file non fa niente.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * La lingua della pagina corrente, 'it' se Polylang non c'è.
 *
 * @return string
 */
function lidia_lingua() {
	if ( function_exists( 'pll_current_language' ) ) {
		$lingua = pll_current_language( 'slug' );

		if ( $lingua ) {
			return $lingua;
		}
	}

	return 'it';
}

/**
 * Codici hreflang.
 *
 * Si dichiara una lingua solo se la pagina ha davvero una traduzione pubblicata in
 * quella lingua. Polylang, sulla home, dichiara la home di ogni lingua anche quando
 * non esiste: una pagina EN in bozza, o assente, non deve comparire.
 * Se resta una lingua sola, niente hreflang: dichiarare solo se stessi non serve.
 *
 * @param array $hreflang Codice hreflang => URL.
 * @return array
 */
function lidia_hreflang( $hreflang ) {
	$pagina = is_singular() ? get_queried_object_id() : 0;

	if ( ! $pagina && is_front_page() ) {
		$pagina = (int) get_option( 'page_on_front' );
	}

	$uscita = array();

	foreach ( (array) $hreflang as $codice => $url ) {
		if ( 'x-default' === $codice ) {
			continue;
		}

		$slug = strtolower( substr( (string) $codice, 0, 2 ) );

		if ( $pagina && function_exists( 'pll_get_post' ) ) {
			$traduzione = pll_get_post( $pagina, $slug );

			if ( ! $traduzione || 'publish' !== get_post_status( $traduzione ) ) {
				continue;
			}
		}

		$uscita[ $slug ] = $url;
	}

	if ( count( $uscita ) < 2 ) {
		return array();
	}

	if ( isset( $uscita['it'] ) ) {
		$uscita['x-default'] = $uscita['it'];
	}

	return $uscita;
}
add_filter( 'pll_rel_hreflang_attributes', 'lidia_hreflang' );

/**
 * Testata e footer nella lingua della pagina.
 *
 * @param array $blocco Blocco analizzato, prima del rendering.
 * @return array
 */
function lidia_parti_per_lingua( $blocco ) {
	if ( 'core/template-part' !== $blocco['blockName'] || empty( $blocco['attrs']['slug'] ) ) {
		return $blocco;
	}

	$slug = $blocco['attrs']['slug'];

	if ( ! in_array( $slug, array( 'header', 'footer' ), true ) ) {
		return $blocco;
	}

	$lingua = lidia_lingua();

	if ( 'it' === $lingua || ! is_readable( LIDIA_DIR . '/parts/' . $slug . '-' . $lingua . '.html' ) ) {
		return $blocco;
	}

	$blocco['attrs']['slug'] = $slug . '-' . $lingua;

	return $blocco;
}
add_filter( 'render_block_data', 'lidia_parti_per_lingua' );

/**
 * La pagina corrente, anche quando è la home.
 *
 * @return int
 */
function lidia_lingua_pagina() {
	$pagina = is_singular() ? get_queried_object_id() : 0;

	if ( ! $pagina && is_front_page() ) {
		$pagina = (int) get_option( 'page_on_front' );
	}

	return (int) $pagina;
}

/**
 * Il selettore IT · EN. Vuoto se la pagina non ha la traduzione pubblicata.
 *
 * @return string
 */
function lidia_lingua_render() {
	if ( ! function_exists( 'pll_get_post' ) ) {
		return '';
	}

	$pagina = lidia_lingua_pagina();

	if ( ! $pagina ) {
		return '';
	}

	$attuale = lidia_lingua();
	$altra   = 'en' === $attuale ? 'it' : 'en';

	$traduzione = pll_get_post( $pagina, $altra );

	if ( ! $traduzione || 'publish' !== get_post_status( $traduzione ) ) {
		return '';
	}

	$etichette = array(
		'it' => array( 'IT', 'Versione italiana' ),
		'en' => array( 'EN', 'English version' ),
	);

	$link = '<a href="' . esc_url( get_permalink( $traduzione ) ) . '" hreflang="' . esc_attr( $altra ) . '" lang="' . esc_attr( $altra ) . '" aria-label="' . esc_attr( $etichette[ $altra ][1] ) . '">' . esc_html( $etichette[ $altra ][0] ) . '</a>';
	$qui  = '<span class="lidia-lingua-attuale" aria-current="true">' . esc_html( $etichette[ $attuale ][0] ) . '</span>';
	$sep  = '<span class="lidia-lingua-sep" aria-hidden="true">·</span>';

	// L'ordine resta fisso, IT prima di EN: il selettore non salta da una pagina all'altra.
	$voci = 'it' === $attuale ? $qui . $sep . $link : $link . $sep . $qui;

	return '<p class="lidia-lingua has-minuto-font-size">' . $voci . '</p>';
}

/** Registra il blocco del selettore. Dinamico, senza script: il markup lo fa PHP. */
function lidia_registra_lingua() {
	register_block_type(
		'lidia/lingua',
		array(
			'api_version'     => 3,
			'title'           => __( 'Selettore di lingua', 'lidia' ),
			'category'        => 'widgets',
			'icon'            => 'translation',
			'description'     => __( 'IT · EN nella testata: porta alla stessa pagina nell’altra lingua, solo se la traduzione è pubblicata.', 'lidia' ),
			'render_callback' => 'lidia_lingua_render',
			'supports'        => array(
				'html'     => false,
				'inserter' => false,
			),
		)
	);
}
add_action( 'init', 'lidia_registra_lingua' );
