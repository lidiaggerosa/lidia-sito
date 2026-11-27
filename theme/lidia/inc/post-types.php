<?php
/**
 * Custom post type e tassonomie.
 *
 * Solo quelli che servono davvero: `caso` resta fuori finché non esistono i contenuti
 * (decisione dell'11/09). `landing` sta in inc/landing.php. `webinar` registrato il 18/11/2026
 * con il primo webinar Teams (decisione del 14/09: «si registra quando c'è il primo»).
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * CPT `risorsa` — i whitepaper.
 *
 * URL: /risorse/whitepaper/{slug}/.
 * `has_archive` è **false** di proposito: l'indice /risorse/whitepaper/ è una pagina
 * vera, con introduzione, FAQ e un blocco query. Così il team marketing può scrivere
 * l'introduzione senza toccare un template.
 */
function lidia_registra_risorsa() {
	register_post_type(
		'risorsa',
		array(
			'labels'            => array(
				'name'          => __( 'Whitepaper', 'lidia' ),
				'singular_name' => __( 'Whitepaper', 'lidia' ),
				'add_new_item'  => __( 'Aggiungi whitepaper', 'lidia' ),
				'edit_item'     => __( 'Modifica whitepaper', 'lidia' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'menu_icon'         => 'dashicons-media-document',
			'menu_position'     => 21,
			'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'has_archive'       => false,
			'rewrite'           => array(
				'slug'       => 'risorse/whitepaper',
				'with_front' => false,
			),
			'taxonomies'        => array( 'tipo-risorsa', 'settore' ),
		)
	);
}
add_action( 'init', 'lidia_registra_risorsa' );

/**
 * CPT `webinar` — una pagina per webinar Teams, con modulo di iscrizione.
 *
 * URL: /risorse/webinar/{slug}/ (albero approvato il 14/09). Indicizzabile, fuori dal menu.
 * Nessun archivio: /risorse/webinar/ non esiste finché non serve un elenco.
 * Data, ora, ID Teams e webinar successivo: campi della pagina (inc/meta.php).
 */
function lidia_registra_webinar() {
	register_post_type(
		'webinar',
		array(
			'labels'        => array(
				'name'          => __( 'Webinar', 'lidia' ),
				'singular_name' => __( 'Webinar', 'lidia' ),
				'add_new_item'  => __( 'Aggiungi webinar', 'lidia' ),
				'edit_item'     => __( 'Modifica webinar', 'lidia' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-video-alt2',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'excerpt', 'custom-fields', 'revisions' ),
			'has_archive'   => false,
			'rewrite'       => array(
				'slug'       => 'risorse/webinar',
				'with_front' => false,
			),
			'taxonomies'    => array( 'settore' ),
		)
	);
}
add_action( 'init', 'lidia_registra_webinar' );

/**
 * Tassonomie condivise fra whitepaper e articoli.
 *
 * `tipo-risorsa` e `settore` non compaiono negli URL: servono a filtrare e a
 * raggruppare, non a creare pagine di archivio da far indicizzare.
 */
function lidia_registra_tassonomie() {
	register_taxonomy(
		'tipo-risorsa',
		array( 'risorsa', 'post' ),
		array(
			'labels'            => array(
				'name'          => __( 'Tipi di risorsa', 'lidia' ),
				'singular_name' => __( 'Tipo di risorsa', 'lidia' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		'settore',
		array( 'risorsa', 'post', 'webinar' ),
		array(
			'labels'            => array(
				'name'          => __( 'Settori', 'lidia' ),
				'singular_name' => __( 'Settore', 'lidia' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'lidia_registra_tassonomie' );
