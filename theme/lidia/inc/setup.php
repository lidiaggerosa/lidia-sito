<?php
/**
 * Supporti del tema, blocchi ammessi e pulizia dell'head.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Supporti minimi. Niente di superfluo: ogni supporto aggiunge markup o comportamento.
 */
function lidia_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	// I pattern core e la pattern directory remota portano stili e composizioni estranee
	// al design system: si tolgono, così l'inserter mostra solo i pattern di Lidia.
	remove_theme_support( 'core-block-patterns' );

	load_theme_textdomain( 'lidia', LIDIA_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'lidia_setup' );

/**
 * Disattiva il fetch dei pattern remoti di WordPress.org.
 */
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * I blocchi ammessi quando si scrive una pagina o un contenuto.
 *
 * Il design system è fatto di poche forme. Tutto quello che non è in questa lista o
 * rompe la griglia e la scala tipografica (Cover, Media & Text, colonne libere, spaziatori
 * arbitrari), o mette codice e contenuto nel database invece che nei file (HTML
 * personalizzato, shortcode, classico, blocchi riusabili), o è un widget di WordPress che
 * non c'entra con questo sito (feed, calendari, tag cloud, commenti, social).
 *
 * Non è una de-registrazione: i blocchi restano registrati e continuano a renderizzare
 * dove già esistono. Sparisce l'inseritore, che è il punto: nessuno li può aggiungere.
 *
 * La lista vale solo nell'editor dei contenuti. Nell'editor del sito (template e parti)
 * i blocchi core servono tutti, e chi ci lavora sa cosa sta facendo.
 *
 * Se serve una forma nuova, si aggiunge qui e si motiva nel decision log — non si aggira.
 *
 * @param bool|string[]             $ammessi  Blocchi ammessi, o true per tutti.
 * @param WP_Block_Editor_Context   $contesto Contesto dell'editor.
 * @return bool|string[]
 */
function lidia_blocchi_ammessi( $ammessi, $contesto ) {
	if ( empty( $contesto->post ) ) {
		return $ammessi;
	}

	return array(
		// Testo.
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/table',
		'core/details',

		// Struttura e figure.
		'core/group',
		'core/image',
		'core/separator',
		'core/buttons',
		'core/button',

		// Elenchi automatici: gli hub di /risorse/.
		'core/query',
		'core/post-template',
		'core/post-title',
		'core/post-excerpt',
		'core/post-date',
		'core/query-no-results',
		'core/query-pagination',
		'core/query-pagination-previous',
		'core/query-pagination-numbers',
		'core/query-pagination-next',

		// Blocchi del tema.
		'lidia/modulo',

		// I mock delle funzioni (24/09/2026): widget HTML autonomi generati fuori dal tema,
		// incollati nella colonna destra del box funzione. Decisione dell'owner, che qui
		// deroga alla regola «niente codice nel database»: vedi docs/10a-guida-box-funzione.md.
		'core/html',
	);
}
add_filter( 'allowed_block_types_all', 'lidia_blocchi_ammessi', 10, 2 );

/**
 * I loghi clienti si caricano pigri.
 *
 * Sono diciotto immagini sotto la piega, ripetute per il nastro: trecento kilobyte che
 * il browser non deve scaricare per mostrare la prima schermata. WordPress non ci arriva
 * da solo perché il lazy loading automatico richiede width e height sull'immagine, e
 * questi loghi sono dimensionati dal CSS.
 *
 * Si aggancia alla classe `lidia-logo`, che è già il marcatore del design system: vale
 * ovunque compaia la fascia, oggi e domani.
 *
 * @param string $html  Markup del blocco.
 * @param array  $blocco Blocco.
 * @return string
 */
function lidia_loghi_pigri( $html, $blocco ) {
	if ( empty( $blocco['attrs']['className'] ) || false === strpos( $blocco['attrs']['className'], 'lidia-logo' ) ) {
		return $html;
	}

	if ( false !== strpos( $html, 'loading=' ) ) {
		return $html;
	}

	return str_replace( '<img ', '<img loading="lazy" decoding="async" ', $html );
}
add_filter( 'render_block_core/image', 'lidia_loghi_pigri', 10, 2 );

/**
 * Pulizia dell'head: markup che non serve e che costa richieste o byte.
 */
function lidia_cleanup_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Emoji: script, stili e filtri.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'lidia_cleanup_head' );

/**
 * Dashicons solo per chi è loggato: sul front-end pubblico non servono.
 */
function lidia_dequeue_dashicons() {
	if ( ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'lidia_dequeue_dashicons', 100 );
