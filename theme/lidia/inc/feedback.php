<?php
/**
 * Questionario di feedback prodotto (29/10/2026).
 *
 * Una pagina, /feedback/ (utenti attuali, con NPS). Il questionario trial è escluso (decisione del 29/10).
 * Template `page-feedback`: solo logo in alto, solo riga legale in fondo.
 * Il contenuto è un blocco dinamico, `lidia/feedback`:
 * domande e opzioni stanno qui, nel tema, non nel database.
 *
 * Le risposte vanno in Notion, non in Delera: non sono lead.
 * Endpoint: POST /wp-json/lidia/v1/feedback/utenti.
 * In wp-config.php: LIDIA_NOTION_TOKEN, LIDIA_NOTION_DB_UTENTI.
 *
 * Pagina `noindex, nofollow` (meta e intestazione), fuori dalla sitemap di Yoast e dalla
 * ricerca interna, fuori dai menu. Il tracking del sito non le vede: tracking.js osserva
 * solo `form.lidia-modulo`.
 *
 * Runbook: scripts/28-feedback.md.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/** Slug della pagina => tipo di questionario. */
const LIDIA_FB_PAGINE = array(
	'feedback' => 'utenti',
);

/** Secondi minimi fra apertura della pagina e invio. */
const LIDIA_FB_ATTESA = 4;

/** Invii ammessi per connessione ogni 10 minuti. */
const LIDIA_FB_TETTO = 10;

/* -------------------------------------------------------------------------
 * Le domande. Valore => etichetta: il valore è quello che arriva a Notion
 * (le opzioni delle select del database), l'etichetta è quella a schermo.
 * ---------------------------------------------------------------------- */

/**
 * Le funzioni di Lidia.
 *
 * @return array<string, string>
 */
function lidia_fb_funzioni() {
	return array(
		'Ricerca legale'       => 'Ricerca legale',
		'Workflow'             => 'Workflow',
		'Smart answer'         => 'Smart answer',
		'Conversazione libera' => 'Conversazione libera (AI Assistant)',
		'Add-in Word'          => 'Add-in Word',
	);
}

/** @return array<string, string> */
function lidia_fb_frequenze() {
	return array(
		'Ogni giorno'           => 'Ogni giorno',
		'Più volte a settimana' => 'Più volte a settimana',
		'Qualche volta al mese' => 'Qualche volta al mese',
		'Raramente'             => 'Raramente',
	);
}

/** @return array<string, string> */
function lidia_fb_diversa() {
	return array(
		'Molto diversa'    => 'Molto diversa',
		'In parte diversa' => 'In parte diversa',
		'Simile'           => 'Simile',
		'Non so'           => 'Non so',
	);
}

/** @return array<string, string> */
function lidia_fb_agente() {
	return array(
		'Sì'                            => 'Sì',
		"Forse (dipende dall'attività)" => "Forse, dipende dall'attività",
		'No'                            => 'No',
	);
}

/** @return array<string, string> */
function lidia_fb_ambiti() {
	$ambiti = array(
		'Due diligence',
		'Contratti (redazione e revisione)',
		'Contenzioso e atti processuali',
		'Ricerca approfondita su norme e giurisprudenza',
		'Pareri e memorie',
		'Compliance e conformità normativa',
		'Monitoraggio di novità normative e giurisprudenziali',
		'Fiscale e tax',
	);

	return array_combine( $ambiti, $ambiti );
}

/* -------------------------------------------------------------------------
 * Pagine: riconoscimento, robots, sitemap, ricerca, CSS e JS.
 * ---------------------------------------------------------------------- */

/**
 * La pagina richiesta è un questionario?
 *
 * @return bool
 */
function lidia_fb_e_pagina() {
	return is_page( array_keys( LIDIA_FB_PAGINE ) );
}

/**
 * ID delle pagine dei questionari che esistono.
 *
 * @return int[]
 */
function lidia_fb_id_pagine() {
	$ids = array();

	foreach ( array_keys( LIDIA_FB_PAGINE ) as $slug ) {
		$pagina = get_page_by_path( $slug );
		if ( $pagina ) {
			$ids[] = (int) $pagina->ID;
		}
	}

	return $ids;
}

/**
 * `noindex, nofollow` nel meta di WordPress.
 *
 * @param array $robots Direttive.
 * @return array
 */
function lidia_fb_robots( $robots ) {
	if ( ! lidia_fb_e_pagina() ) {
		return $robots;
	}

	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	unset( $robots['index'], $robots['follow'] );

	return $robots;
}
add_filter( 'wp_robots', 'lidia_fb_robots', 20 );

/**
 * Lo stesso nel meta di Yoast.
 *
 * @param string $robots Valore di Yoast.
 * @return string
 */
function lidia_fb_robots_yoast( $robots ) {
	return lidia_fb_e_pagina() ? 'noindex, nofollow' : $robots;
}
add_filter( 'wpseo_robots', 'lidia_fb_robots_yoast' );

/** Intestazione X-Robots-Tag e niente cache di pagina: la marca temporale deve essere fresca. */
function lidia_fb_intestazioni() {
	if ( ! lidia_fb_e_pagina() ) {
		return;
	}

	header( 'X-Robots-Tag: noindex, nofollow', true );
	nocache_headers();
}
add_action( 'template_redirect', 'lidia_fb_intestazioni' );

/**
 * Fuori dalla sitemap di Yoast.
 *
 * @param array $esclusi ID già esclusi.
 * @return array
 */
function lidia_fb_sitemap( $esclusi ) {
	return array_merge( (array) $esclusi, lidia_fb_id_pagine() );
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'lidia_fb_sitemap' );

/**
 * Fuori dalla ricerca interna.
 *
 * @param WP_Query $query Query.
 */
function lidia_fb_ricerca( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$ids = lidia_fb_id_pagine();

	if ( $ids ) {
		$query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $ids ) );
	}
}
add_action( 'pre_get_posts', 'lidia_fb_ricerca' );

/** CSS e JS solo sui questionari; la cornice delle pagine di testo qui non serve. */
function lidia_fb_enqueue() {
	if ( ! lidia_fb_e_pagina() ) {
		return;
	}

	wp_dequeue_style( 'lidia-pagina' );

	wp_enqueue_style(
		'lidia-feedback',
		LIDIA_URI . '/assets/css/sezioni/feedback.css',
		array( 'lidia-tokens', 'lidia-base' ),
		lidia_versione_file( 'assets/css/sezioni/feedback.css' )
	);

	wp_enqueue_script(
		'lidia-feedback',
		LIDIA_URI . '/assets/js/feedback.js',
		array(),
		lidia_versione_file( 'assets/js/feedback.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'lidia_fb_enqueue', 20 );

/* -------------------------------------------------------------------------
 * Blocco lidia/feedback: il markup del questionario.
 * ---------------------------------------------------------------------- */

/** Registra il blocco dinamico. Non è nell'allowlist dell'editor: non si inserisce a mano. */
function lidia_fb_registra_blocco() {
	register_block_type(
		'lidia/feedback',
		array(
			'api_version'     => 3,
			'title'           => __( 'Questionario feedback', 'lidia' ),
			'category'        => 'widgets',
			'attributes'      => array(
				'tipo' => array(
					'type'    => 'string',
					'default' => 'utenti',
				), // unico valore oggi; resta per un eventuale secondo questionario
			),
			'render_callback' => 'lidia_fb_render',
		)
	);
}
add_action( 'init', 'lidia_fb_registra_blocco' );

/**
 * Marca temporale firmata, per il controllo dei secondi minimi.
 *
 * @return string
 */
function lidia_fb_marca() {
	$ora = (string) time();

	return $ora . '.' . hash_hmac( 'sha256', $ora, wp_salt( 'lidia-feedback' ) );
}

/**
 * Età in secondi di una marca valida, o -1.
 *
 * @param string $marca Marca ricevuta.
 * @return int
 */
function lidia_fb_eta_marca( $marca ) {
	$pezzi = explode( '.', (string) $marca, 2 );

	if ( 2 !== count( $pezzi ) || ! hash_equals( hash_hmac( 'sha256', $pezzi[0], wp_salt( 'lidia-feedback' ) ), $pezzi[1] ) ) {
		return -1;
	}

	return time() - (int) $pezzi[0];
}

/**
 * Gruppo di scelte (radio o checkbox) a pillole.
 *
 * @param string $nome    Nome del campo.
 * @param array  $opzioni Valore => etichetta.
 * @param string $tipo    radio | checkbox.
 * @param array  $extra   Attributi per valore (es. data-esclusivo).
 * @return string
 */
function lidia_fb_pillole( $nome, $opzioni, $tipo = 'radio', $extra = array() ) {
	$html = '<div class="lidia-fb-opzioni">';

	foreach ( $opzioni as $valore => $etichetta ) {
		$attr = isset( $extra[ $valore ] ) ? ' ' . $extra[ $valore ] : '';
		$html .= sprintf(
			'<label class="lidia-fb-pillola"><input type="%1$s" name="%2$s" value="%3$s"%4$s><span>%5$s</span></label>',
			esc_attr( $tipo ),
			esc_attr( $nome ),
			esc_attr( $valore ),
			$attr,
			esc_html( $etichetta )
		);
	}

	return $html . '</div>';
}

/**
 * Domanda a scelta in un fieldset.
 *
 * @param string $domanda Testo.
 * @param string $nome    Campo.
 * @param array  $opzioni Valore => etichetta.
 * @param string $tipo    radio | checkbox.
 * @param string $nota    Nota sotto la domanda.
 * @param string $attr    Attributi del fieldset.
 * @param array  $extra   Attributi per valore.
 * @return string
 */
function lidia_fb_scelta( $domanda, $nome, $opzioni, $tipo = 'radio', $nota = '', $attr = '', $extra = array() ) {
	$nota = $nota ? '<span class="lidia-fb-nota-domanda">' . esc_html( $nota ) . '</span>' : '';

	return '<fieldset class="lidia-fb-domanda"' . ( $attr ? ' ' . $attr : '' ) . '><legend>' . esc_html( $domanda ) . $nota . '</legend>'
		. lidia_fb_pillole( $nome, $opzioni, $tipo, $extra ) . '</fieldset>';
}

/**
 * Riga di voto 1–5, con «Non la uso» se richiesto.
 *
 * @param string $nome      Campo.
 * @param string $etichetta Testo.
 * @param string $non_uso   Etichetta dell'opzione «non usata» (es. «Non la uso»); vuota per nessuna.
 * @return string
 */
function lidia_fb_voto( $nome, $etichetta, $non_uso = '' ) {
	$opzioni = array_combine( array( '1', '2', '3', '4', '5' ), array( '1', '2', '3', '4', '5' ) );
	$extra   = array();

	if ( $non_uso ) {
		$opzioni['na'] = $non_uso;
		$extra['na']   = 'data-non-uso';
	}

	$id = 'lidia-fb-l-' . $nome;

	return '<div class="lidia-fb-voto" role="radiogroup" aria-labelledby="' . esc_attr( $id ) . '"><span class="lidia-fb-voto-etichetta" id="' . esc_attr( $id ) . '">'
		. esc_html( $etichetta ) . '</span>' . lidia_fb_pillole( $nome, $opzioni, 'radio', $extra ) . '</div>';
}

/**
 * Campo di testo (area o riga).
 *
 * @param string $domanda Etichetta.
 * @param string $nome    Campo.
 * @param string $tipo    textarea | text | email.
 * @param int    $max     Lunghezza massima.
 * @param string $extra   Attributi in più.
 * @return string
 */
function lidia_fb_testo( $domanda, $nome, $tipo = 'textarea', $max = 1500, $extra = '' ) {
	$id    = 'lidia-fb-' . $nome;
	$campo = 'textarea' === $tipo
		? sprintf( '<textarea id="%1$s" name="%2$s" maxlength="%3$d" rows="3"></textarea>', esc_attr( $id ), esc_attr( $nome ), $max )
		: sprintf( '<input type="%1$s" id="%2$s" name="%3$s" maxlength="%4$d"%5$s>', esc_attr( $tipo ), esc_attr( $id ), esc_attr( $nome ), $max, $extra ? ' ' . $extra : '' );

	return '<div class="lidia-fb-domanda"><label class="lidia-fb-etichetta" for="' . esc_attr( $id ) . '">' . esc_html( $domanda ) . '</label>' . $campo . '</div>';
}

/**
 * Apertura di una sezione.
 *
 * @param string $titolo       Titolo.
 * @param string $obbligatorie Campi obbligatori, separati da virgola.
 * @return string
 */
function lidia_fb_sezione( $titolo, $obbligatorie = '' ) {
	return '<section class="lidia-fb-sezione" data-obbligatorie="' . esc_attr( $obbligatorie ) . '" hidden><h2 tabindex="-1">' . esc_html( $titolo ) . '</h2>';
}

/**
 * Chiusura di una sezione con errore e bottone.
 *
 * @param bool $ultima Ultima sezione: bottone di invio.
 * @return string
 */
function lidia_fb_fine_sezione( $ultima = false ) {
	$bottone = $ultima
		? '<button type="submit" class="wp-element-button">Invia</button>'
		: '<button type="button" class="wp-element-button" data-avanti>Continua</button>';

	return '<p class="lidia-fb-errore" role="alert"></p>' . $bottone . '</section>';
}

/** Nota sulla scala. */
function lidia_fb_scala() {
	return '<p class="lidia-fb-nota">Valuta da 1 (insufficiente) a 5 (ottimo).</p>';
}

/**
 * Il questionario per gli utenti attuali.
 *
 * @return string
 */
function lidia_fb_domande_utenti() {
	$h  = lidia_fb_sezione( 'Come usi Lidia', 'frequenza' );
	$h .= lidia_fb_scelta( 'Con che frequenza usi Lidia?', 'frequenza', lidia_fb_frequenze() );
	$h .= lidia_fb_scelta(
		'Quali funzioni usi?',
		'funzioni',
		lidia_fb_funzioni() + array( 'Non so' => 'Non so' ),
		'checkbox',
		'Puoi selezionare più opzioni.',
		'',
		array( 'Non so' => 'data-esclusivo' )
	);
	$h .= lidia_fb_fine_sezione();

	$h .= lidia_fb_sezione( 'Le funzioni', 'v_ricerca,v_workflow,v_smart,v_chat,v_word' );
	$h .= lidia_fb_scala();
	$h .= '<div class="lidia-fb-voti">';
	$h .= lidia_fb_voto( 'v_ricerca', 'Ricerca legale', 'Non la uso' );
	$h .= lidia_fb_voto( 'v_workflow', 'Workflow', 'Non la uso' );
	$h .= lidia_fb_voto( 'v_smart', 'Smart answer', 'Non la uso' );
	$h .= lidia_fb_voto( 'v_chat', 'Conversazione libera (AI Assistant)', 'Non la uso' );
	$h .= lidia_fb_voto( 'v_word', 'Add-in Word', 'Non la uso' );
	$h .= '</div>';
	$h .= lidia_fb_testo( 'Quale funzione ti è più utile? Perché?', 'piu_utile' );
	$h .= lidia_fb_testo( 'Quale funzione usi poco o per nulla? Perché?', 'poco_usata' );
	$h .= lidia_fb_fine_sezione();

	$h .= lidia_fb_sezione( 'Rispetto agli altri strumenti', 'diversa' );
	$h .= lidia_fb_scelta( 'Rispetto agli altri strumenti che usi (altre piattaforme legal AI, AI generaliste, banche dati), Lidia è:', 'diversa', lidia_fb_diversa() );
	$h .= lidia_fb_testo( 'Se diversa: in cosa?', 'in_cosa' );
	$h .= lidia_fb_testo( 'Quali altri strumenti usi per la stessa attività?', 'altri', 'text', 200 );
	$h .= lidia_fb_fine_sezione();

	$h .= lidia_fb_sezione( 'Qualità percepita', 'fiducia,fonti,qualita,facilita,velocita' );
	$h .= lidia_fb_scala();
	$h .= '<div class="lidia-fb-voti">';
	$h .= lidia_fb_voto( 'fiducia', 'Fiducia nei risultati' );
	$h .= lidia_fb_voto( 'fonti', 'Completezza delle fonti' );
	$h .= lidia_fb_voto( 'qualita', 'Qualità delle risposte' );
	$h .= lidia_fb_voto( 'facilita', "Facilità d'uso" );
	$h .= lidia_fb_voto( 'velocita', 'Velocità' );
	$h .= lidia_fb_voto( 'formazione', 'Formazione ricevuta (onboarding e sessioni con il team)', 'Non ne ho usufruito' );
	$h .= lidia_fb_voto( 'helpdesk', 'Assistenza (helpdesk)', 'Non ne ho usufruito' );
	$h .= '</div>';
	$h .= lidia_fb_fine_sezione();

	$h .= lidia_fb_sezione( 'Miglioramenti' );
	$h .= lidia_fb_testo( "Cosa ti manca? Una funzione, un'integrazione, altro.", 'manca' );
	$h .= lidia_fb_testo( 'Se potessi cambiare una sola cosa in Lidia, quale sarebbe?', 'cambiare' );
	$h .= lidia_fb_scelta( 'Ti piacerebbe avere in Lidia un agente che svolga da solo attività complesse, lasciandoti solo la verifica finale?', 'agente', lidia_fb_agente() );
	$h .= '<div data-mostra-se="agente" data-tranne="No" hidden>';
	$h .= lidia_fb_scelta( 'In quali ambiti lo useresti?', 'agente_ambiti', lidia_fb_ambiti(), 'checkbox' );
	$h .= lidia_fb_testo( 'Altro ambito (facoltativo)', 'agente_altro', 'text', 200 );
	$h .= '</div>';
	$h .= lidia_fb_fine_sezione();

	$nps = array();
	for ( $i = 0; $i <= 10; $i++ ) {
		$nps[ (string) $i ] = (string) $i;
	}

	$h .= lidia_fb_sezione( 'Il tuo voto', 'nps' );
	$h .= '<fieldset class="lidia-fb-domanda lidia-fb-nps"><legend>Quanto è probabile che consiglieresti Lidia a un collega?</legend>'
		. lidia_fb_pillole( 'nps', $nps ) . '<p class="lidia-fb-nota">0 = per niente probabile · 10 = molto probabile</p></fieldset>';
	$h .= lidia_fb_testo( 'Qual è il motivo principale del tuo voto?', 'motivo' );
	$h .= lidia_fb_fine_sezione( true );

	return $h;
}

/**
 * Render del blocco.
 *
 * @param array $attributi Attributi del blocco.
 * @return string
 */
function lidia_fb_render( $attributi ) {
	$tipo    = 'utenti';
	$titolo  = 'Il tuo parere su Lidia';
	$intro   = 'Lidia cresce con il lavoro di chi la usa ogni giorno. Dicci cosa funziona, cosa no e cosa ti serve: bastano cinque minuti, e le risposte arrivano al team prodotto.';
	$domande = lidia_fb_domande_utenti();

	$azione = rest_url( 'lidia/v1/feedback/' . $tipo );

	return '<div class="lidia-feedback" data-lidia-feedback>'
		. '<div class="lidia-fb-intro"><h1>' . esc_html( $titolo ) . '</h1><p>' . esc_html( $intro ) . '</p></div>'
		. '<form class="lidia-fb-modulo" action="' . esc_url( $azione ) . '" method="post" data-tipo="' . esc_attr( $tipo ) . '" novalidate>'
		. '<div class="lidia-fb-avanzamento" aria-live="polite"><b data-numero></b><span data-titolo></span></div>'
		. '<div class="lidia-fb-barra"><i data-barra></i></div>'
		. $domande
		. '<div class="lidia-fb-esca" aria-hidden="true"><label>Sito web<input type="text" name="sito_web" tabindex="-1" autocomplete="off"></label></div>'
		. '<input type="hidden" name="marca" value="' . esc_attr( lidia_fb_marca() ) . '">'
		. '<p class="lidia-fb-privacy">Usiamo le risposte solo per migliorare Lidia. <a href="/legale/privacy/" target="_blank" rel="noopener">Informativa privacy</a></p>'
		. '</form>'
		. '<div class="lidia-fb-grazie" tabindex="-1" hidden><h1>Grazie.</h1><p>Abbiamo ricevuto le tue risposte.</p></div>'
		. '<noscript><p class="lidia-fb-nota">Per compilare il questionario serve JavaScript attivo nel browser.</p></noscript>'
		. '</div>';
}

/* -------------------------------------------------------------------------
 * Endpoint REST → Notion.
 * ---------------------------------------------------------------------- */

/** Registra la rotta. */
function lidia_fb_rotta() {
	register_rest_route(
		'lidia/v1',
		'/feedback/(?P<tipo>utenti)',
		array(
			'methods'             => 'POST',
			'callback'            => 'lidia_fb_ricevi',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'lidia_fb_rotta' );

/**
 * Indirizzo del visitatore: stesso criterio del modulo (LIDIA_DIETRO_PROXY).
 *
 * @return string
 */
function lidia_fb_ip() {
	if ( function_exists( 'lidia_modulo_ip' ) ) {
		return lidia_modulo_ip();
	}

	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'ignoto';
}

/**
 * Riceve un questionario e lo scrive in Notion.
 *
 * @param WP_REST_Request $richiesta Richiesta.
 * @return WP_REST_Response
 */
function lidia_fb_ricevi( WP_REST_Request $richiesta ) {
	$b = $richiesta->get_json_params();

	if ( ! is_array( $b ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}

	// Antispam: esca compilata, tempo mancante o sotto la soglia (lato pagina e lato server).
	// Si finge il successo senza salvare.
	$ms  = isset( $b['ms'] ) && is_numeric( $b['ms'] ) ? (int) $b['ms'] : 0;
	$eta = lidia_fb_eta_marca( isset( $b['marca'] ) ? (string) $b['marca'] : '' );

	if ( ! empty( $b['sito_web'] ) || $ms < LIDIA_FB_ATTESA * 1000 || $eta < LIDIA_FB_ATTESA ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	// Limite per connessione. L'IP non si scrive mai in chiaro.
	$chiave = 'lidia_fb_' . substr( hash_hmac( 'sha256', lidia_fb_ip(), wp_salt( 'lidia-feedback' ) ), 0, 20 );
	$invii  = (int) get_transient( $chiave );

	if ( $invii >= LIDIA_FB_TETTO ) {
		return new WP_REST_Response( array( 'ok' => false ), 429 );
	}

	set_transient( $chiave, $invii + 1, 10 * MINUTE_IN_SECONDS );

	$proprieta = lidia_fb_proprieta_utenti( $b );

	if ( null === $proprieta ) {
		return new WP_REST_Response( array( 'ok' => false ), 400 );
	}

	$database = defined( 'LIDIA_NOTION_DB_UTENTI' ) ? LIDIA_NOTION_DB_UTENTI : '';

	if ( ! defined( 'LIDIA_NOTION_TOKEN' ) || ! LIDIA_NOTION_TOKEN || ! $database ) {
		error_log( '[lidia-feedback] costanti Notion mancanti in wp-config.php' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return new WP_REST_Response( array( 'ok' => false ), 500 );
	}

	$risposta = wp_remote_post(
		'https://api.notion.com/v1/pages',
		array(
			'timeout' => 15,
			'headers' => array(
				'Authorization'  => 'Bearer ' . LIDIA_NOTION_TOKEN,
				'Notion-Version' => '2022-06-28',
				'Content-Type'   => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'parent'     => array( 'database_id' => $database ),
					'properties' => $proprieta,
				)
			),
		)
	);

	if ( is_wp_error( $risposta ) || 200 !== wp_remote_retrieve_response_code( $risposta ) ) {
		error_log( '[lidia-feedback] errore Notion: ' . ( is_wp_error( $risposta ) ? $risposta->get_error_message() : wp_remote_retrieve_body( $risposta ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return new WP_REST_Response( array( 'ok' => false ), 502 );
	}

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/* -------------------------------------------------------------------------
 * Conversione nei tipi di proprietà di Notion.
 * ---------------------------------------------------------------------- */

/**
 * @param mixed $v   Valore.
 * @param int   $max Lunghezza massima.
 * @return string
 */
function lidia_fb_pulisci( $v, $max = 1900 ) {
	return mb_substr( trim( sanitize_textarea_field( (string) $v ) ), 0, $max );
}

/** @param mixed $v Valore. */
function lidia_fb_n_testo( $v ) {
	$v = lidia_fb_pulisci( $v );
	return '' === $v ? null : array( 'rich_text' => array( array( 'text' => array( 'content' => $v ) ) ) );
}

/**
 * @param mixed $v       Valore.
 * @param array $ammessi Valori ammessi.
 */
function lidia_fb_n_select( $v, $ammessi ) {
	return ( is_string( $v ) && in_array( $v, $ammessi, true ) ) ? array( 'select' => array( 'name' => $v ) ) : null;
}

/**
 * @param mixed $lista   Valori.
 * @param array $ammessi Valori ammessi.
 */
function lidia_fb_n_multi( $lista, $ammessi ) {
	$out = array();

	foreach ( (array) $lista as $v ) {
		if ( is_string( $v ) && in_array( $v, $ammessi, true ) ) {
			$out[] = array( 'name' => $v );
		}
	}

	return $out ? array( 'multi_select' => $out ) : null;
}

/**
 * @param mixed $v   Valore.
 * @param int   $min Minimo.
 * @param int   $max Massimo.
 */
function lidia_fb_n_numero( $v, $min, $max ) {
	if ( null === $v || '' === $v || ! is_numeric( $v ) ) {
		return null;
	}

	$n = (int) $v;

	return ( $n >= $min && $n <= $max ) ? array( 'number' => $n ) : null;
}

/** @param string $s Testo. */
function lidia_fb_n_titolo( $s ) {
	return array( 'title' => array( array( 'text' => array( 'content' => $s ) ) ) );
}

/**
 * Proprietà del database utenti. Null se manca l'NPS.
 *
 * @param array $b Corpo della richiesta.
 * @return array|null
 */
function lidia_fb_proprieta_utenti( $b ) {
	$prendi = function ( $k ) use ( $b ) {
		return isset( $b[ $k ] ) ? $b[ $k ] : null;
	};
	$voti   = isset( $b['voti'] ) && is_array( $b['voti'] ) ? $b['voti'] : array();
	$voto   = function ( $k ) use ( $voti ) {
		return lidia_fb_n_numero( isset( $voti[ $k ] ) ? $voti[ $k ] : null, 1, 5 );
	};

	$nps = lidia_fb_n_numero( $prendi( 'nps' ), 0, 10 );

	if ( null === $nps ) {
		return null;
	}

	$proprieta = array(
		'Risposta'                  => lidia_fb_n_titolo( 'NPS ' . $nps['number'] . ' · ' . wp_date( 'Y-m-d H:i' ) ),
		'Frequenza uso'             => lidia_fb_n_select( $prendi( 'frequenza' ), array_keys( lidia_fb_frequenze() ) ),
		'Funzioni usate'            => lidia_fb_n_multi( $prendi( 'funzioni' ), array_merge( array_keys( lidia_fb_funzioni() ), array( 'Non so' ) ) ),
		'Voto Ricerca legale'       => $voto( 'ricerca' ),
		'Voto Workflow'             => $voto( 'workflow' ),
		'Voto Smart answer'         => $voto( 'smart' ),
		'Voto Conversazione libera' => $voto( 'chat' ),
		'Voto Add-in Word'          => $voto( 'word' ),
		'Funzione più utile'        => lidia_fb_n_testo( $prendi( 'piu_utile' ) ),
		'Funzione poco usata'       => lidia_fb_n_testo( $prendi( 'poco_usata' ) ),
		'Diversa dagli altri'       => lidia_fb_n_select( $prendi( 'diversa' ), array_keys( lidia_fb_diversa() ) ),
		'In cosa è diversa'         => lidia_fb_n_testo( $prendi( 'in_cosa' ) ),
		'Altri strumenti'           => lidia_fb_n_testo( $prendi( 'altri' ) ),
		'Fiducia nei risultati'     => lidia_fb_n_numero( $prendi( 'fiducia' ), 1, 5 ),
		'Completezza fonti'         => lidia_fb_n_numero( $prendi( 'fonti' ), 1, 5 ),
		'Qualità risposte'          => lidia_fb_n_numero( $prendi( 'qualita' ), 1, 5 ),
		"Facilità d'uso"            => lidia_fb_n_numero( $prendi( 'facilita' ), 1, 5 ),
		'Velocità'                  => lidia_fb_n_numero( $prendi( 'velocita' ), 1, 5 ),
		'Voto formazione'           => lidia_fb_n_numero( $prendi( 'formazione' ), 1, 5 ),
		'Voto helpdesk'             => lidia_fb_n_numero( $prendi( 'helpdesk' ), 1, 5 ),
		'Cosa manca'                => lidia_fb_n_testo( $prendi( 'manca' ) ),
		'Una cosa da cambiare'      => lidia_fb_n_testo( $prendi( 'cambiare' ) ),
		'Agente interesse'          => lidia_fb_n_select( $prendi( 'agente' ), array_keys( lidia_fb_agente() ) ),
		'Agente ambiti'             => lidia_fb_n_multi( $prendi( 'agente_ambiti' ), array_keys( lidia_fb_ambiti() ) ),
		'Agente altro'              => lidia_fb_n_testo( $prendi( 'agente_altro' ) ),
		'NPS'                       => $nps,
		'Motivo del voto'           => lidia_fb_n_testo( $prendi( 'motivo' ) ),
		'Stato'                     => array( 'select' => array( 'name' => 'Nuovo' ) ),
	);

	return array_filter(
		$proprieta,
		function ( $p ) {
			return null !== $p;
		}
	);
}
