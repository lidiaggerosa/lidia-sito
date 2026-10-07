<?php
/**
 * Polylang: lingue e impostazioni. Rieseguibile.
 *
 *   wp plugin install polylang --activate
 *   wp eval-file scripts/17-polylang.php
 *
 * - Italiano predefinito, alla radice e senza prefisso; inglese sotto /en/ (decision log 11/09).
 * - Nessun reindirizzamento automatico per lingua del browser: un crawler va sempre
 *   dove l'URL dice, e chi arriva su una pagina italiana resta lì.
 * - La home inglese risponde su /en/: /en/home-en/ va in 301 lì (07/10/2026). La vecchia
 *   /en/ di Joomla aveva 491 click in 12 mesi e oggi finiva su uno slug interno.
 * - Tutti i contenuti che esistono sono italiani: chi è senza lingua la prende qui.
 * - Le traduzioni si collegano con scripts/18-pagine-en.md, non da questo script.
 *
 * @package Lidia
 */

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	WP_CLI::error( 'Polylang non è attivo.' );
}

$modello = PLL()->model;

/* 1. Lingue ------------------------------------------------------------ */

$lingue = array(
	array(
		'name'       => 'Italiano',
		'slug'       => 'it',
		'locale'     => 'it_IT',
		'rtl'        => false,
		'term_group' => 0,
		'flag'       => 'it',
	),
	array(
		'name'       => 'English',
		'slug'       => 'en',
		'locale'     => 'en_GB',
		'rtl'        => false,
		'term_group' => 1,
		'flag'       => 'gb',
	),
);

foreach ( $lingue as $lingua ) {
	if ( $modello->languages->get( $lingua['slug'] ) ) {
		WP_CLI::log( "= lingua {$lingua['slug']} già presente" );
		continue;
	}

	$esito = $modello->languages->add( $lingua );

	if ( is_wp_error( $esito ) ) {
		WP_CLI::error( $esito->get_error_message() );
	}

	WP_CLI::log( "+ lingua {$lingua['slug']}" );
}

$modello->languages->update_default( 'it' );

/* 2. Impostazioni ------------------------------------------------------ */

$impostazioni = array(
	'force_lang'    => 1,               // lingua dalla directory: /en/…
	'hide_default'  => true,            // italiano senza /it/
	'rewrite'       => true,            // niente /language/ nell'URL
	'redirect_lang' => true,            // home EN su /en/, non su /en/home-en/ (07/10/2026)
	'browser'       => false,           // nessun redirect per lingua del browser
	'media_support' => false,           // le immagini non si traducono
	'post_types'    => array( 'risorsa' ),
	'taxonomies'    => array(),
	'sync'          => array(),
);

foreach ( $impostazioni as $chiave => $valore ) {
	$modello->options[ $chiave ] = $valore;
	WP_CLI::log( "  opzione {$chiave}" );
}

if ( method_exists( $modello->options, 'save' ) ) {
	$modello->options->save();
}

// Gli URL delle home per lingua stanno in cache (transient pll_languages_list): senza pulirla
// redirect_lang non ha effetto e /en/ continua a rimandare a /en/home-en/ (verificato il 07/10).
$modello->clean_languages_cache();
delete_transient( 'pll_languages_list' );

/* 3. Contenuti esistenti in italiano ----------------------------------- */

$senza = get_posts(
	array(
		'post_type'      => array( 'page', 'post', 'risorsa' ),
		'post_status'    => array( 'publish', 'private', 'draft' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$assegnati = 0;

foreach ( $senza as $id ) {
	if ( ! pll_get_post_language( $id ) ) {
		pll_set_post_language( $id, 'it' );
		++$assegnati;
	}
}

WP_CLI::log( "  {$assegnati} contenuti assegnati all'italiano" );

flush_rewrite_rules( false );

WP_CLI::success( 'Polylang configurato.' );
