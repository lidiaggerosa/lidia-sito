<?php
/**
 * Server MCP in inglese: pagina /en/product/mcp-server/, box nella home EN e riquadro su
 * /en/product/. Rieseguibile (23/10/2026).
 *
 *   wp eval-file scripts/27-mcp-en.php --user=gianluca
 *
 * Crea la pagina EN se non c'è (pubblicata, figlia di /en/product/, collegata con Polylang a
 * /prodotto/integrazione-mcp/), altrimenti ne aggiorna il contenuto. Poi aggiorna home EN e
 * /en/product/ dai file di blocchi e scrive i metadati Yoast della pagina.
 * Testi approvati da Gianluca il 23/10/2026, in content/en/mcp-server.md.
 *
 * @package Lidia
 */

if ( ! function_exists( 'pll_get_post' ) ) {
	WP_CLI::error( 'Polylang non è attivo.' );
}

// Il contenuto viene dai nostri file: nessun filtro che tolga attributi o SVG (l'animazione).
kses_remove_filters();

/**
 * Contenuto di un file di blocchi, pronto per wp_insert_post / wp_update_post.
 *
 * @param string $file Nome del file in scripts/blocchi/en/.
 * @return string
 */
function lidia_mcp_en_file( $file ) {
	$percorso = __DIR__ . '/blocchi/en/' . $file;
	if ( ! is_readable( $percorso ) ) {
		WP_CLI::error( "File mancante: {$percorso}" );
	}
	return wp_slash( file_get_contents( $percorso ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

$it_prodotto = get_page_by_path( 'prodotto', OBJECT, 'page' );
$it_mcp      = get_page_by_path( 'prodotto/integrazione-mcp', OBJECT, 'page' );

if ( ! $it_prodotto || ! $it_mcp ) {
	WP_CLI::error( 'Manca /prodotto/ o /prodotto/integrazione-mcp/ in italiano.' );
}

$en_prodotto = (int) pll_get_post( $it_prodotto->ID, 'en' );
$en_home     = (int) pll_get_post( (int) get_option( 'page_on_front' ), 'en' );

if ( ! $en_prodotto || ! $en_home ) {
	WP_CLI::error( 'Manca la home EN o /en/product/: lanciare prima 18-pagine-en.php.' );
}

// 1. La pagina.
$en_mcp = (int) pll_get_post( $it_mcp->ID, 'en' );

if ( $en_mcp ) {
	$esito = wp_update_post(
		array(
			'ID'           => $en_mcp,
			'post_content' => lidia_mcp_en_file( 'mcp-server.html' ),
			'post_status'  => 'publish',
		),
		true
	);
	$azione = 'aggiornata';
} else {
	$esito = wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_title'    => 'Lidia, inside your AI assistant.',
			'post_name'     => 'mcp-server',
			'post_parent'   => $en_prodotto,
			'post_content'  => lidia_mcp_en_file( 'mcp-server.html' ),
			'page_template' => 'page-composta',
		),
		true
	);
	$azione = 'creata';
}

if ( is_wp_error( $esito ) ) {
	WP_CLI::error( $esito->get_error_message() );
}

$en_mcp = (int) $esito;

pll_set_post_language( $en_mcp, 'en' );
$traduzioni       = pll_get_post_translations( $it_mcp->ID );
$traduzioni['it'] = $it_mcp->ID;
$traduzioni['en'] = $en_mcp;
pll_save_post_translations( $traduzioni );

update_post_meta( $en_mcp, '_yoast_wpseo_title', 'Lidia MCP server: Lidia inside Claude and ChatGPT | Lidia' );
update_post_meta( $en_mcp, '_yoast_wpseo_metadesc', 'With the Lidia MCP server you call Lidia from Claude, ChatGPT or another AI assistant: legal research on official sources, matters and workflows.' );
update_post_meta( $en_mcp, '_yoast_wpseo_focuskw', 'legal MCP server' );
update_post_meta( $en_mcp, '_yoast_wpseo_bctitle', 'MCP server' );

WP_CLI::log( sprintf( '  /en/product/mcp-server/ → %d, %s (%s)', $en_mcp, $azione, get_permalink( $en_mcp ) ) );

// 2. Box nella home EN e riquadro su /en/product/.
foreach ( array( $en_home => 'home.html', $en_prodotto => 'product.html' ) as $id => $file ) {
	$esito = wp_update_post(
		array(
			'ID'           => $id,
			'post_content' => lidia_mcp_en_file( $file ),
		),
		true
	);
	if ( is_wp_error( $esito ) ) {
		WP_CLI::error( $esito->get_error_message() );
	}
	WP_CLI::log( sprintf( '  %-24s → %d ← %s', get_permalink( $id ), $id, $file ) );
}

WP_CLI::success( 'Server MCP in inglese pronto. Poi: wp rewrite flush e wp sg purge.' );
