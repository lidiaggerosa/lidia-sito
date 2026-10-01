<?php
/**
 * Bootstrap del tema Lidia.
 *
 * Qui dentro vanno solo costanti e require. Ogni comportamento sta in un file di inc/,
 * uno per responsabilità: così si trova, e si può togliere senza cercarlo.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

define( 'LIDIA_VERSION', '1.0.0-alpha' );
define( 'LIDIA_DIR', get_template_directory() );
define( 'LIDIA_URI', get_template_directory_uri() );

/**
 * File di inc/ da caricare, nell'ordine.
 *
 * Un file assente non blocca il sito: durante la costruzione del tema alcuni non esistono ancora.
 */
foreach (
	array(
		'setup',         // supporti del tema, blocchi ammessi, pulizia dell'head
		'enqueue',       // font self-hosted, tokens.css, base.css, CSS per sezione
		'patterns',      // categorie dei pattern, rimozione dei pattern core e remoti
		'post-types',    // CPT risorsa + tassonomie
		'meta',          // register_post_meta + pannello «Documento protetto»
		'libreria',      // blocco lidia/libreria: la pagina /libreria/ con tutte le sezioni
		'legale',        // noindex, nofollow e fuori sitemap per il ramo /legale/
		'schema',        // JSON-LD non coperto da Yoast: Organization, FAQPage, SoftwareApplication
		'seo',           // robots.txt e llms.txt generati dal tema
		'icona',         // favicon e icone dai file di assets/icone/, non dal pannello
		'lingue',        // Polylang: hreflang, testata e footer per lingua
		'forms',         // blocco lidia/modulo: markup, validazione, antispam, ricezione
		'delera',        // consegna al webhook, riprove, coda, download firmato
	) as $lidia_inc
) {
	$lidia_file = LIDIA_DIR . '/inc/' . $lidia_inc . '.php';
	if ( is_readable( $lidia_file ) ) {
		require_once $lidia_file;
	}
}
unset( $lidia_inc, $lidia_file );
