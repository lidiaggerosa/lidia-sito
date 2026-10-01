<?php
/**
 * Le pagine inglesi: create in bozza e collegate alla loro pagina italiana. Rieseguibile.
 *
 *   wp eval-file scripts/18-pagine-en.php
 *
 * Crea solo le pagine che non esistono: se la traduzione c'è già, non la tocca (né il
 * contenuto né lo stato). Il contenuto arriva dopo, dai file in scripts/blocchi/en/,
 * con `wp post update <ID> <file>`, come per l'italiano. Si pubblica quando il testo
 * è approvato: `wp post update <ID> --post_status=publish`.
 *
 * Perimetro deciso il 24/09/2026: sette pagine. Risorse e legali restano solo in italiano.
 * Il 01/10/2026 si aggiunge Careers, figlia di Company: è la destinazione del 301 da /en/careers.
 * Serve Polylang configurato (scripts/17-polylang.php).
 *
 * @package Lidia
 */

if ( ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang non è attivo.' );
}

/*
 * Percorso della pagina italiana => titolo e slug della pagina inglese.
 * La home inglese risponde a /en/ perché è la traduzione della pagina iniziale:
 * il suo slug non compare nell'URL (Polylang free non ammette slug uguali fra lingue).
 */
$pagine = array(
	'/'               => array( 'Legal intelligence that works with you.', 'home-en' ),
	'/prodotto/'      => array( 'Product', 'product' ),
	'/sicurezza/'     => array( 'Security', 'security' ),
	'/prezzi/'        => array( 'Pricing', 'pricing' ),
	'/prova-gratuita/' => array( 'Free trial', 'free-trial' ),
	'/azienda/'       => array( 'Company', 'company' ),
	'/contatti/'      => array( 'Contact', 'contact' ),
	'/azienda/lavora-con-noi/' => array( 'Careers', 'careers' ),
);

foreach ( $pagine as $percorso => $dati ) {
	list( $titolo, $slug ) = $dati;

	if ( '/' === $percorso ) {
		$italiana = (int) get_option( 'page_on_front' );
	} else {
		$pagina   = get_page_by_path( trim( $percorso, '/' ), OBJECT, 'page' );
		$italiana = $pagina ? (int) $pagina->ID : 0;
	}

	if ( ! $italiana ) {
		WP_CLI::warning( "Pagina italiana non trovata: {$percorso}" );
		continue;
	}

	if ( 'it' !== pll_get_post_language( $italiana ) ) {
		pll_set_post_language( $italiana, 'it' );
	}

	$esistente = pll_get_post( $italiana, 'en' );

	if ( $esistente ) {
		WP_CLI::log( sprintf( '= %-26s → %d (%s), già presente', $percorso, $esistente, get_post_status( $esistente ) ) );
		continue;
	}

	$template = get_page_template_slug( $italiana );

	// Una figlia italiana ha come genitore la traduzione inglese del suo genitore:
	// /azienda/lavora-con-noi/ diventa /en/company/careers/. Le figlie vanno dopo il genitore.
	$genitore_it = (int) wp_get_post_parent_id( $italiana );
	$genitore_en = $genitore_it ? (int) pll_get_post( $genitore_it, 'en' ) : 0;

	if ( $genitore_it && ! $genitore_en ) {
		WP_CLI::warning( "Manca la pagina EN del genitore di {$percorso}: si salta" );
		continue;
	}

	$id = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'draft',
			'post_title'    => $titolo,
			'post_name'     => $slug,
			'post_content'  => '',
			'post_parent'   => $genitore_en,
			'page_template' => $template ? $template : '',
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}

	pll_set_post_language( $id, 'en' );

	$traduzioni       = pll_get_post_translations( $italiana );
	$traduzioni['it'] = $italiana;
	$traduzioni['en'] = $id;
	pll_save_post_translations( $traduzioni );

	WP_CLI::log( sprintf( '+ %-26s → %d, bozza, template «%s»', $percorso, $id, $template ? $template : 'default' ) );
}

WP_CLI::success( 'Pagine EN pronte in bozza.' );
