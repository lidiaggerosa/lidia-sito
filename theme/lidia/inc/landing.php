<?php
/**
 * Landing page di campagna (Fase 6, 12/10/2026).
 *
 * CPT `landing`, URL /lp/{slug}/. Pagine isolate per il traffico a pagamento:
 * template `single-landing` con testata e footer ridotti (parts/lp-testata, parts/lp-piede),
 * `noindex, nofollow`, fuori dalla sitemap di Yoast e dalla ricerca interna.
 * Spec: docs/05-landing-pages.md. Contenuti: scripts/23-landing.md.
 *
 * Il CSS delle sezioni lo carica già enqueue.php leggendo il contenuto; qui si aggiunge
 * solo landing.css e si toglie la cornice delle pagine di testo, che sulle landing non serve.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * CPT `landing`.
 *
 * Nessun archivio: /lp/ non esiste. Nessuna tassonomia `campagna` per ora: la campagna
 * arriva a Delera dagli UTM, che il modulo raccoglie già su ogni pagina.
 */
function lidia_registra_landing() {
	register_post_type(
		'landing',
		array(
			'labels'              => array(
				'name'          => __( 'Landing', 'lidia' ),
				'singular_name' => __( 'Landing', 'lidia' ),
				'add_new_item'  => __( 'Aggiungi landing', 'lidia' ),
				'edit_item'     => __( 'Modifica landing', 'lidia' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 22,
			'supports'            => array( 'title', 'editor', 'revisions', 'custom-fields' ),
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => 'lp',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'lidia_registra_landing' );

/**
 * `noindex, nofollow` su ogni landing: non devono competere con le pagine SEO del sito.
 *
 * Stessa regola di inc/legale.php: sta nel tema, non in una spunta di Yoast nel database.
 *
 * @param array $robots Direttive raccolte da WordPress.
 * @return array
 */
function lidia_robots_landing( $robots ) {
	if ( ! is_singular( 'landing' ) ) {
		return $robots;
	}

	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['index'], $robots['follow'] );

	return $robots;
}
add_filter( 'wp_robots', 'lidia_robots_landing', 20 );

/**
 * Lo stesso per il meta robots di Yoast, se lo stampa lui.
 *
 * @param string $robots Valore di Yoast.
 * @return string
 */
function lidia_robots_landing_yoast( $robots ) {
	return is_singular( 'landing' ) ? 'noindex, nofollow' : $robots;
}
add_filter( 'wpseo_robots', 'lidia_robots_landing_yoast' );

/**
 * Fuori dalla sitemap di Yoast, tutto il tipo di contenuto.
 *
 * @param bool   $escluso Già escluso.
 * @param string $tipo    Tipo di contenuto.
 * @return bool
 */
function lidia_sitemap_esclude_landing( $escluso, $tipo ) {
	return 'landing' === $tipo ? true : $escluso;
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'lidia_sitemap_esclude_landing', 10, 2 );

/**
 * CSS delle landing: landing.css sì, cornice delle pagine di testo no.
 */
function lidia_enqueue_landing() {
	if ( ! is_singular( 'landing' ) ) {
		return;
	}

	wp_dequeue_style( 'lidia-pagina' );

	wp_enqueue_style(
		'lidia-landing',
		LIDIA_URI . '/assets/css/sezioni/landing.css',
		array( 'lidia-tokens', 'lidia-base' ),
		lidia_versione_file( 'assets/css/sezioni/landing.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'lidia_enqueue_landing', 20 );

/**
 * Nell'editor le sezioni delle landing si vedono come sul sito.
 */
function lidia_editor_landing() {
	add_editor_style( 'assets/css/sezioni/landing.css' );
}
add_action( 'after_setup_theme', 'lidia_editor_landing', 11 );
