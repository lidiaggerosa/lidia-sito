<?php
/**
 * Le pagine inglesi: contenuto dai file di blocchi e metadati Yoast. Rieseguibile.
 *
 *   wp eval-file scripts/19-contenuti-en.php
 *
 * Trova ogni pagina EN come traduzione Polylang della pagina italiana (gli ID di locale e
 * staging non coincidono), ne sostituisce il contenuto con scripts/blocchi/en/<file>.html
 * e scrive title, description e keyword di Yoast. Lo stato non si tocca: le pagine restano
 * in bozza finché non c'è l'informativa privacy in inglese (decisione del 25/09/2026).
 *
 * Testi approvati da Gianluca il 25/09/2026, in content/en/.
 *
 * @package Lidia
 */

if ( ! function_exists( 'pll_get_post' ) ) {
	WP_CLI::error( 'Polylang non è attivo: prima scripts/17-polylang.php e 18-pagine-en.php.' );
}

// Il contenuto viene dai nostri file: nessun filtro che tolga attributi o SVG.
kses_remove_filters();

$cartella = __DIR__ . '/blocchi/en/';

$pagine = array(
	'/'                => array(
		'file'  => 'home.html',
		'title' => 'Legal AI for law firms and companies | Lidia AI',
		'desc'  => 'Lidia is the legal AI that works with you: research on official sources, document analysis and drafting, built into Word. 7-day free trial.',
		'kw'    => 'legal AI',
	),
	'/prodotto/'       => array(
		'file'  => 'product.html',
		'title' => 'AI software for law firms: features | Lidia',
		'desc'  => "Lidia's features, AI software for law firms: official-source research with GraphRAG, workflows, Word add-in, AI Assistant, Smart Answer.",
		'kw'    => 'AI software for law firms',
	),
	'/sicurezza/'      => array(
		'file'  => 'security.html',
		'title' => 'Data security and AI in law firms | Lidia',
		'desc'  => 'Data security for law firms using AI: data on AWS in the European Union, no access for model providers, ISO 27001, CSA STAR, GDPR.',
		'kw'    => 'AI data security for law firms',
	),
	'/prezzi/'         => array(
		'file'  => 'pricing.html',
		'title' => 'What does AI software for law firms cost? | Lidia',
		'desc'  => 'Lidia starts at €125 a month. Lidia Professional includes every feature, every area of law and access to official sources.',
		'kw'    => 'legal AI pricing',
	),
	'/prova-gratuita/' => array(
		'file'  => 'free-trial.html',
		'title' => 'Free legal AI software trial — 7 days | Lidia',
		'desc'  => "A seven-day free trial on your firm's real documents. Every feature enabled, official sources included, data in the European Union.",
		'kw'    => 'legal AI free trial',
	),
	'/azienda/'        => array(
		'file'  => 'company.html',
		'title' => 'The team behind Italian legal AI | Lidia',
		'desc'  => 'Lidia S.r.l. builds legal intelligence that works alongside your firm. Designed by lawyers with experience at leading Italian and international firms.',
		'kw'    => 'Lidia legal AI',
	),
	'/contatti/'       => array(
		'file'  => 'contact.html',
		'title' => 'Contact | Lidia',
		'desc'  => 'Contact Lidia S.r.l.: lidia@lidiatech.ai, +39 010 8991141. Offices in Genoa and Milan. For free trials, please use the dedicated form.',
		'kw'    => 'Lidia contact',
	),
);

foreach ( $pagine as $percorso => $p ) {
	if ( '/' === $percorso ) {
		$italiana = (int) get_option( 'page_on_front' );
	} else {
		$pagina   = get_page_by_path( trim( $percorso, '/' ), OBJECT, 'page' );
		$italiana = $pagina ? (int) $pagina->ID : 0;
	}

	$inglese = $italiana ? (int) pll_get_post( $italiana, 'en' ) : 0;

	if ( ! $inglese ) {
		WP_CLI::warning( "Nessuna pagina EN collegata a {$percorso}: lanciare prima 18-pagine-en.php" );
		continue;
	}

	$file = $cartella . $p['file'];

	if ( ! is_readable( $file ) ) {
		WP_CLI::error( "File mancante: {$file}" );
	}

	$esito = wp_update_post(
		array(
			'ID'           => $inglese,
			'post_content' => wp_slash( file_get_contents( $file ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		),
		true
	);

	if ( is_wp_error( $esito ) ) {
		WP_CLI::error( $esito->get_error_message() );
	}

	update_post_meta( $inglese, '_yoast_wpseo_title', $p['title'] );
	update_post_meta( $inglese, '_yoast_wpseo_metadesc', $p['desc'] );
	update_post_meta( $inglese, '_yoast_wpseo_focuskw', $p['kw'] );

	WP_CLI::log( sprintf( '  %-17s → %d (%s) ← %s', $percorso, $inglese, get_post_status( $inglese ), $p['file'] ) );
}

WP_CLI::success( 'Pagine EN aggiornate. Lo stato non è cambiato.' );
