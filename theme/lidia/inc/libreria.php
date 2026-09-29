<?php
/**
 * La libreria delle sezioni: /libreria/.
 *
 * Una pagina che mostra, una sotto l'altra, tutte le sezioni del design system così
 * come il tema le renderizza oggi, ciascuna con la sua scheda: nome nell'inseritore,
 * slug, categoria, descrizione e le pagine in cui è impiegata. Serve a chi rivede
 * testi e grafica (si vede tutto in un colpo solo) e a chi compone pagine nuove
 * dall'admin (sa cosa c'è e come si chiama).
 *
 * È un blocco dinamico, `lidia/libreria`: legge i pattern registrati al momento
 * della richiesta. Cambia il tema, cambia la libreria. Non c'è niente da tenere
 * aggiornato a mano.
 *
 * La pagina non è per il pubblico: si crea `private` (scripts/14-libreria.md), è
 * `noindex, nofollow` per costruzione e resta fuori dalla sitemap.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/** Slug della pagina. */
const LIDIA_LIBRERIA_SLUG = 'libreria';

/**
 * Le pagine di servizio: private, `noindex, nofollow`, fuori dalla sitemap.
 *
 * La libreria mostra le sezioni; il laboratorio (24/09/2026) è il banco di prova
 * in cui si compongono e si misurano componenti nuovi dall'editor, prima di
 * portarli in una pagina vera.
 *
 * @return string[] Slug.
 */
function lidia_pagine_servizio() {
	return array( LIDIA_LIBRERIA_SLUG, 'laboratorio' );
}

/**
 * Le categorie dei pattern, nell'ordine in cui la libreria le mostra.
 *
 * @return array<string, string> slug => etichetta.
 */
function lidia_libreria_categorie() {
	return array(
		'lidia-home'      => __( 'Sezioni della home', 'lidia' ),
		'lidia-sezioni'   => __( 'Sezioni delle pagine interne', 'lidia' ),
		'lidia-contenuti' => __( 'Ossature dei contenuti', 'lidia' ),
		'lidia-gesti'     => __( 'Gesti in archivio (fuori uso dal 16/09/2026)', 'lidia' ),
	);
}

/**
 * I pattern del tema, raggruppati per categoria e ordinati per titolo.
 *
 * @return array<string, array<int, array>>
 */
function lidia_libreria_pattern() {
	$registro = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
	$gruppi   = array_fill_keys( array_keys( lidia_libreria_categorie() ), array() );
	$altri    = array();

	foreach ( $registro as $pattern ) {
		if ( empty( $pattern['name'] ) || 0 !== strpos( $pattern['name'], 'lidia/' ) ) {
			continue;
		}

		$categoria = ! empty( $pattern['categories'] ) ? (string) $pattern['categories'][0] : '';

		if ( isset( $gruppi[ $categoria ] ) ) {
			$gruppi[ $categoria ][] = $pattern;
		} else {
			$altri[] = $pattern;
		}
	}

	if ( $altri ) {
		$gruppi['altri'] = $altri;
	}

	foreach ( $gruppi as &$lista ) {
		usort(
			$lista,
			function ( $a, $b ) {
				return strnatcasecmp( $a['title'], $b['title'] );
			}
		);
	}
	unset( $lista );

	return array_filter( $gruppi );
}

/**
 * La classe che identifica una sezione: l'ultima classe del primo gruppo del pattern.
 *
 * Le sezioni si aprono tutte con un gruppo `lidia-sezione lidia-<pagina> lidia-<sezione>`:
 * l'ultima è la più specifica ed è quella che si cerca nei contenuti per sapere dove
 * la sezione è impiegata.
 *
 * @param string $contenuto Markup del pattern.
 * @return string
 */
function lidia_libreria_classe_sezione( $contenuto ) {
	if ( ! preg_match( '/<!-- wp:group \{[^}]*"className":"([^"]+)"/', $contenuto, $m ) ) {
		return '';
	}

	$classi = preg_split( '/\s+/', trim( $m[1] ) );

	return $classi ? end( $classi ) : '';
}

/**
 * Le pagine pubblicate in cui compare una classe di sezione.
 *
 * Una sola query per tutta la libreria: i contenuti si leggono una volta e si
 * cercano in memoria.
 *
 * @return array<int, array{titolo: string, url: string, contenuto: string}>
 */
function lidia_libreria_contenuti() {
	static $contenuti = null;

	if ( null !== $contenuti ) {
		return $contenuti;
	}

	$contenuti = array();
	$post      = get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'risorsa' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	foreach ( $post as $p ) {
		if ( LIDIA_LIBRERIA_SLUG === $p->post_name ) {
			continue;
		}

		$contenuti[] = array(
			'titolo'    => get_the_title( $p ),
			'url'       => get_permalink( $p ),
			'contenuto' => $p->post_content,
		);
	}

	return $contenuti;
}

/**
 * Dove è impiegata una sezione.
 *
 * @param string $classe Classe di sezione.
 * @return array<int, array{titolo: string, url: string}>
 */
function lidia_libreria_dove( $classe ) {
	if ( '' === $classe ) {
		return array();
	}

	$dove = array();

	foreach ( lidia_libreria_contenuti() as $c ) {
		if ( false !== strpos( $c['contenuto'], $classe ) ) {
			$dove[] = array(
				'titolo' => $c['titolo'],
				'url'    => $c['url'],
			);
		}
	}

	return $dove;
}

/**
 * Il campionario tipografico: titoli, prosa, occhiello, elenco, citazione, i tre bottoni.
 *
 * Markup di blocchi, così quello che si vede è esattamente ciò che l'editor produce.
 *
 * @return string
 */
function lidia_libreria_campionario() {
	return '<!-- wp:group {"className":"lidia-sezione lidia-libreria-campionario","align":"full","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group alignfull lidia-sezione lidia-libreria-campionario"><!-- wp:group {"className":"lidia-colonna","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-colonna"><!-- wp:paragraph {"className":"lidia-occhiello"} -->
<p class="lidia-occhiello">Occhiello · paragrafo con classe lidia-occhiello</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Titolo di primo livello, Fraunces</h1>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Titolo di secondo livello: apre una sezione</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Titolo di terzo livello, dentro una sezione</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Titolo di quarto livello, per le schede</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Paragrafo di prosa in Inter, alla misura di lettura della colonna. Il sito parla a professionisti che diffidano dell’hype: registro competente, asciutto, concreto, con prove. Un <a href="#">link nel testo</a> e un <strong>grassetto</strong> sono tutto ciò che serve.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Voce di elenco: una frase compiuta, non una parola.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Seconda voce, per vedere il ritmo verticale.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>Una citazione: la voce di un avvocato o di una fonte, con l’attribuzione sotto.</p>
<!-- /wp:paragraph --><cite>Nome Cognome, ruolo, studio</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Bottone primario</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-secondario"} -->
<div class="wp-block-button is-style-secondario"><a class="wp-block-button__link wp-element-button" href="#">Bottone secondario</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-terziario"} -->
<div class="wp-block-button is-style-terziario"><a class="wp-block-button__link wp-element-button" href="#">Bottone terziario</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * La scheda che precede ogni sezione.
 *
 * @param array  $pattern Pattern registrato.
 * @param string $classe  Classe di sezione.
 * @return string
 */
function lidia_libreria_scheda( $pattern, $classe ) {
	$dove   = lidia_libreria_dove( $classe );
	$titolo = isset( $pattern['title'] ) ? (string) $pattern['title'] : $pattern['name'];
	$descr  = isset( $pattern['description'] ) ? (string) $pattern['description'] : '';
	$ancora = sanitize_title( str_replace( '/', '-', $pattern['name'] ) );

	$html  = '<div class="lidia-libreria-scheda" id="' . esc_attr( $ancora ) . '">';
	$html .= '<div class="lidia-libreria-scheda__riga">';
	$html .= '<span class="lidia-libreria-scheda__nome">' . esc_html( $titolo ) . '</span>';
	$html .= '<span class="lidia-libreria-scheda__slug">' . esc_html( $pattern['name'] ) . '</span>';

	if ( $classe ) {
		$html .= '<span class="lidia-libreria-scheda__slug">.' . esc_html( $classe ) . '</span>';
	}

	if ( isset( $pattern['inserter'] ) && false === $pattern['inserter'] ) {
		$html .= '<span class="lidia-libreria-scheda__stato">' . esc_html__( 'non nell’inseritore', 'lidia' ) . '</span>';
	}

	$html .= '</div>';

	if ( $descr ) {
		$html .= '<p class="lidia-libreria-scheda__descrizione">' . esc_html( $descr ) . '</p>';
	}

	$html .= '<p class="lidia-libreria-scheda__dove">';

	if ( $dove ) {
		$link = array();
		foreach ( $dove as $d ) {
			$link[] = '<a href="' . esc_url( $d['url'] ) . '">' . esc_html( $d['titolo'] ) . '</a>';
		}
		$html .= esc_html__( 'Impiegata in:', 'lidia' ) . ' ' . implode( ', ', $link );
	} else {
		$html .= esc_html__( 'Non impiegata in nessuna pagina pubblicata.', 'lidia' );
	}

	$html .= '</p></div>';

	return $html;
}

/**
 * L'indice in testa alla pagina: una riga per categoria, un link per sezione.
 *
 * @param array $gruppi Pattern raggruppati.
 * @return string
 */
function lidia_libreria_indice( $gruppi ) {
	$etichette = lidia_libreria_categorie();
	$html      = '<nav class="lidia-libreria-indice" aria-label="' . esc_attr__( 'Indice della libreria', 'lidia' ) . '">';
	$html     .= '<p class="lidia-libreria-indice__riga"><span class="lidia-libreria-indice__categoria">' . esc_html__( 'Campionario', 'lidia' ) . '</span> <a href="#lidia-campionario">' . esc_html__( 'Tipografia e bottoni', 'lidia' ) . '</a></p>';

	foreach ( $gruppi as $categoria => $lista ) {
		$etichetta = isset( $etichette[ $categoria ] ) ? $etichette[ $categoria ] : $categoria;
		$link      = array();

		foreach ( $lista as $pattern ) {
			$ancora = sanitize_title( str_replace( '/', '-', $pattern['name'] ) );
			$link[] = '<a href="#' . esc_attr( $ancora ) . '">' . esc_html( $pattern['title'] ) . '</a>';
		}

		$html .= '<p class="lidia-libreria-indice__riga"><span class="lidia-libreria-indice__categoria">' . esc_html( $etichetta ) . '</span> ' . implode( ' · ', $link ) . '</p>';
	}

	return $html . '</nav>';
}

/**
 * Render del blocco.
 *
 * @return string
 */
function lidia_libreria_render() {
	$gruppi    = lidia_libreria_pattern();
	$etichette = lidia_libreria_categorie();
	$totale    = array_sum( array_map( 'count', $gruppi ) );

	$html  = '<div ' . get_block_wrapper_attributes( array( 'class' => 'lidia-libreria' ) ) . '>';
	$html .= '<div class="lidia-libreria-testa alignfull"><div class="lidia-colonna">';
	$html .= '<p class="lidia-occhiello">' . esc_html__( 'Libreria delle sezioni', 'lidia' ) . '</p>';
	$html .= '<h1>' . esc_html__( 'Tutto quello che il tema sa disegnare', 'lidia' ) . '</h1>';
	$html .= '<p>' . sprintf(
		/* translators: 1: numero di sezioni, 2: data. */
		esc_html__( '%1$d sezioni, così come il tema le renderizza adesso (%2$s). Ogni sezione è preceduta dalla sua scheda: nome nell’inseritore, slug, classe, descrizione e pagine in cui è impiegata. Pagina privata: la vedono solo gli utenti che hanno fatto accesso.', 'lidia' ),
		(int) $totale,
		esc_html( wp_date( 'j/n/Y' ) )
	) . '</p>';
	$html .= lidia_libreria_indice( $gruppi );
	$html .= '</div></div>';

	$html .= '<div class="lidia-libreria-scheda" id="lidia-campionario"><div class="lidia-libreria-scheda__riga"><span class="lidia-libreria-scheda__nome">' . esc_html__( 'Campionario · tipografia e bottoni', 'lidia' ) . '</span></div><p class="lidia-libreria-scheda__descrizione">' . esc_html__( 'I blocchi di base come li disegna il tema: quattro livelli di titolo, prosa, occhiello, elenco, citazione e i tre stili di bottone.', 'lidia' ) . '</p></div>';
	$html .= do_blocks( lidia_libreria_campionario() );

	foreach ( $gruppi as $categoria => $lista ) {
		$etichetta = isset( $etichette[ $categoria ] ) ? $etichette[ $categoria ] : $categoria;
		$html     .= '<h2 class="lidia-libreria-categoria" id="' . esc_attr( sanitize_title( $categoria ) ) . '">' . esc_html( $etichetta ) . ' <span>' . count( $lista ) . '</span></h2>';

		foreach ( $lista as $pattern ) {
			$classe = lidia_libreria_classe_sezione( $pattern['content'] );
			$html  .= lidia_libreria_scheda( $pattern, $classe );
			$html  .= '<div class="lidia-libreria-esemplare">' . do_blocks( $pattern['content'] ) . '</div>';
		}
	}

	return $html . '</div>';
}

/** Registra il blocco e il suo script per l'editor. */
function lidia_registra_libreria() {
	wp_register_script(
		'lidia-libreria-editor',
		LIDIA_URI . '/assets/js/libreria-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		lidia_versione_file( 'assets/js/libreria-editor.js' ),
		true
	);

	register_block_type(
		'lidia/libreria',
		array(
			'api_version'     => 3,
			'title'           => __( 'Libreria delle sezioni', 'lidia' ),
			'category'        => 'widgets',
			'icon'            => 'layout',
			'description'     => __( 'Mostra tutte le sezioni del tema con la loro scheda. Solo per la pagina /libreria/.', 'lidia' ),
			'editor_script'   => 'lidia-libreria-editor',
			'render_callback' => 'lidia_libreria_render',
			'supports'        => array(
				'html'     => false,
				'multiple' => false,
			),
		)
	);
}
add_action( 'init', 'lidia_registra_libreria' );

/**
 * La pagina richiesta è una pagina di servizio (libreria, laboratorio)?
 *
 * @return bool
 */
function lidia_e_libreria() {
	if ( ! is_page() ) {
		return false;
	}

	$pagina = get_queried_object();

	return $pagina instanceof WP_Post && in_array( $pagina->post_name, lidia_pagine_servizio(), true );
}

/**
 * Fuori dagli indici, per costruzione: le pagine di servizio sono private, ma se un giorno
 * qualcuno le pubblicasse per sbaglio i motori di ricerca non le prenderebbero comunque.
 *
 * @param array $robots Direttive.
 * @return array
 */
function lidia_robots_libreria( $robots ) {
	if ( ! lidia_e_libreria() ) {
		return $robots;
	}

	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['index'], $robots['follow'] );

	return $robots;
}
add_filter( 'wp_robots', 'lidia_robots_libreria', 20 );

/**
 * Fuori anche dalla sitemap di Yoast.
 *
 * @param array $esclusi ID già esclusi.
 * @return array
 */
function lidia_sitemap_esclude_libreria( $esclusi ) {
	$esclusi = (array) $esclusi;

	foreach ( lidia_pagine_servizio() as $slug ) {
		$pagina = get_page_by_path( $slug );

		if ( $pagina ) {
			$esclusi[] = $pagina->ID;
		}
	}

	return $esclusi;
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'lidia_sitemap_esclude_libreria' );
