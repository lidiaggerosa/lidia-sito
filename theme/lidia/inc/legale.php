<?php
/**
 * Le pagine legali stanno fuori dagli indici.
 *
 * Decisione dell'owner del 17/09/2026: privacy, cookie, termini e DPA sono
 * `noindex, nofollow`, tutte e quattro. Non sono pagine che devono essere
 * trovate da una ricerca: si raggiungono dal footer o da un link diretto.
 *
 * La regola sta qui e non in una spunta di Yoast per una ragione precisa:
 * una spunta vive nel database, si perde se la pagina viene ricreata da
 * `scripts/` e nessuno se ne accorge. Qui vale per costruzione, per tutto il
 * ramo `/legale/`, comprese le pagine che verranno dopo.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * La pagina richiesta sta sotto /legale/?
 *
 * @return bool
 */
function lidia_e_pagina_legale() {
	if ( ! is_page() ) {
		return false;
	}

	$pagina = get_queried_object();

	if ( ! $pagina instanceof WP_Post ) {
		return false;
	}

	if ( 'legale' === $pagina->post_name ) {
		return true;
	}

	foreach ( get_post_ancestors( $pagina ) as $antenato ) {
		if ( 'legale' === get_post_field( 'post_name', $antenato ) ) {
			return true;
		}
	}

	return false;
}

/**
 * `noindex, nofollow` su tutto il ramo legale.
 *
 * @param array $robots Direttive raccolte da WordPress.
 * @return array
 */
function lidia_robots_legale( $robots ) {
	if ( ! lidia_e_pagina_legale() ) {
		return $robots;
	}

	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['index'], $robots['follow'] );

	return $robots;
}
add_filter( 'wp_robots', 'lidia_robots_legale', 20 );

/**
 * Fuori anche dalla sitemap di Yoast.
 *
 * Yoast rispetta già il noindex, ma solo se il valore è nel suo campo: questa
 * esclusione lavora sugli ID, che è ciò che la sitemap guarda davvero.
 *
 * @param array $esclusi ID già esclusi.
 * @return array
 */
function lidia_sitemap_esclude_legale( $esclusi ) {
	$legale = get_page_by_path( 'legale' );

	if ( ! $legale ) {
		return $esclusi;
	}

	$rami = get_pages(
		array(
			'child_of'    => $legale->ID,
			'post_status' => 'publish',
		)
	);

	$ids = array( $legale->ID );

	foreach ( $rami as $pagina ) {
		$ids[] = $pagina->ID;
	}

	return array_merge( (array) $esclusi, $ids );
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'lidia_sitemap_esclude_legale' );
