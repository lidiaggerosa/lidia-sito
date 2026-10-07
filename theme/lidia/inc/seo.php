<?php
/**
 * robots.txt e llms.txt, serviti dal tema.
 *
 * Nessuno dei due è un file fisico in public_html: un file lì non è versionato, e al
 * primo rilascio sparisce o diverge. Qui si generano da codice nel repo.
 * Sul server **non deve esistere** un robots.txt o un llms.txt fisico: avrebbe la
 * precedenza e questo file non verrebbe mai letto.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * robots.txt.
 *
 * Minimale (docs/06-seo-technical.md §5): tutto ammesso tranne l'admin, sitemap dichiarata.
 * I crawler AI (GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended) sono
 * ammessi come tutti gli altri: decisione dell'owner del 24/09/2026. Non hanno un gruppo
 * proprio apposta: un gruppo dedicato farebbe loro ignorare le regole di `*`.
 *
 * Con `blog_public` a 0 (lo staging) resta il «Disallow: /» di WordPress.
 *
 * @param string     $uscita   robots.txt calcolato da WordPress e dai plugin.
 * @param string|int $pubblico Valore di blog_public.
 * @return string
 */
function lidia_robots_txt( $uscita, $pubblico ) {
	if ( '1' !== (string) $pubblico ) {
		return $uscita;
	}

	$righe = array(
		'# lidiatech.ai — generato dal tema (inc/seo.php). Non si modifica dall\'admin.',
		'# Crawler AI ammessi come tutti gli altri (decisione del 24/09/2026).',
		'',
		'User-agent: *',
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'',
		'Sitemap: ' . home_url( '/sitemap_index.xml' ),
	);

	return implode( "\n", $righe ) . "\n";
}
add_filter( 'robots_txt', 'lidia_robots_txt', PHP_INT_MAX, 2 );

/**
 * llms.txt alla radice.
 *
 * Il testo sta in `llms.txt` nella cartella del tema, approvato dall'owner. `{{sito}}`
 * diventa l'indirizzo del sito, così i link valgono in locale, sullo staging e in
 * produzione. Se il file non c'è, la richiesta prosegue e WordPress risponde 404.
 *
 * L'opzione llms.txt di Yoast resta spenta (scripts/09-yoast.php): scriverebbe un file
 * fisico generato da lei, con la precedenza su questo.
 */
function lidia_llms_txt() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$richiesta = strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$percorso  = wp_parse_url( home_url( '/llms.txt' ), PHP_URL_PATH );

	if ( $richiesta !== $percorso ) {
		return;
	}

	$file = LIDIA_DIR . '/llms.txt';

	if ( ! is_readable( $file ) ) {
		return;
	}

	$testo = str_replace( '{{sito}}', untrailingslashit( home_url() ), (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	echo $testo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'init', 'lidia_llms_txt', 0 );

/* -------------------------------------------------------------------------
 * Utenti non elencabili da fuori (07/10/2026)
 *
 * /wp-json/wp/v2/users mostrava a chiunque i nomi utente (anche quello dell'amministratore):
 * è il primo dato che cerca chi prova a forzare l'accesso (docs/06-seo-technical.md §7).
 * Per chi non ha effettuato l'accesso l'endpoint risponde 401; l'editor, che lo usa per
 * scegliere l'autore, continua a funzionare. Stessa cosa per ?author=N, che altrimenti
 * rimanda a /author/<nome-utente>/.
 * ---------------------------------------------------------------------- */

/**
 * Blocca /wp/v2/users ai visitatori anonimi.
 *
 * @param mixed           $risultato Risposta già pronta, o null.
 * @param WP_REST_Server  $server    Server REST.
 * @param WP_REST_Request $richiesta Richiesta.
 * @return mixed
 */
function lidia_rest_utenti_privati( $risultato, $server, $richiesta ) {
	if ( is_user_logged_in() ) {
		return $risultato;
	}

	if ( 0 === strpos( $richiesta->get_route(), '/wp/v2/users' ) ) {
		return new WP_Error( 'rest_forbidden', __( 'Non consentito.', 'lidia' ), array( 'status' => 401 ) );
	}

	return $risultato;
}
add_filter( 'rest_pre_dispatch', 'lidia_rest_utenti_privati', 10, 3 );

/** ?author=N per i visitatori anonimi: alla home, senza passare dal nome utente. */
function lidia_niente_author_query() {
	if ( is_admin() || is_user_logged_in() || ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'lidia_niente_author_query', 0 );

/* -------------------------------------------------------------------------
 * Autore nel feed (07/10/2026)
 *
 * Gli articoli sono firmati Lidia (decisione dell'owner): nel feed RSS `dc:creator`
 * non deve mostrare l'utente WordPress che li ha caricati.
 * ---------------------------------------------------------------------- */

/**
 * @param string $autore Nome dell'autore.
 * @return string
 */
function lidia_autore_feed( $autore ) {
	return is_feed() ? 'Lidia' : $autore;
}
add_filter( 'the_author', 'lidia_autore_feed' );
