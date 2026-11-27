<?php
/**
 * Webinar su Microsoft Teams: date, riga sotto il titolo, iscrizione via Microsoft Graph.
 *
 * Flusso: modulo del sito (tipo `webinar`) → Delera → workflow Delera → POST a
 * /wp-json/lidia/v1/webinar-registration → il sito iscrive la persona al webinar Teams →
 * Teams manda la mail con il link personale. Gli iscritti da LinkedIn entrano in Delera e
 * seguono lo stesso percorso: l'endpoint non sa da dove arriva il lead.
 *
 * Credenziali solo in wp-config.php:
 *   LIDIA_TEAMS_TENANT_ID, LIDIA_TEAMS_CLIENT_ID, LIDIA_TEAMS_CLIENT_SECRET,
 *   LIDIA_TEAMS_SECRET_SCADENZA (AAAA-MM-GG), LIDIA_TEAMS_ORGANIZZATORE (ID Entra
 *   dell'organizzatore, per `wp lidia webinar elenco`), LIDIA_WEBINAR_TOKEN (condiviso con Delera).
 *
 * Il sito non è un archivio di iscritti: c'è solo una coda di riprove, che si svuota.
 * Runbook: scripts/30-webinar.md
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/** Fuso dell'iscritto per Teams (nome Windows). */
const LIDIA_TEAMS_FUSO = 'W. Europe Standard Time';

/** Lingua dell'iscritto per Teams. */
const LIDIA_TEAMS_LINGUA = 'it-IT';

/** Durata presunta quando manca l'ora di fine. */
const LIDIA_WEBINAR_DURATA = 2 * HOUR_IN_SECONDS;

/** Giorni di preavviso sulla scadenza del client secret. */
const LIDIA_TEAMS_PREAVVISO = 30;

/* -------------------------------------------------------------------------
 * Il webinar: date e stato
 * ---------------------------------------------------------------------- */

/**
 * Il webinar pubblicato con questo slug.
 *
 * @param string $slug Slug.
 * @return WP_Post|null
 */
function lidia_webinar_da_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );

	if ( '' === $slug ) {
		return null;
	}

	$post = get_page_by_path( $slug, OBJECT, 'webinar' );

	return ( $post && 'publish' === $post->post_status ) ? $post : null;
}

/**
 * Un campo data del webinar come momento preciso (ora di Roma, dal fuso del sito).
 *
 * @param int    $id    ID del webinar.
 * @param string $campo lidia_webinar_inizio o lidia_webinar_fine.
 * @return DateTimeImmutable|null
 */
function lidia_webinar_momento( $id, $campo ) {
	$valore = (string) get_post_meta( (int) $id, $campo, true );

	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $valore ) ) {
		return null;
	}

	$momento = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $valore, wp_timezone() );

	return $momento ? $momento : null;
}

/**
 * Inizio e fine, in secondi. Senza fine: inizio più la durata presunta.
 *
 * @param int $id ID del webinar.
 * @return array{0:int,1:int}|null
 */
function lidia_webinar_intervallo( $id ) {
	$inizio = lidia_webinar_momento( $id, 'lidia_webinar_inizio' );

	if ( ! $inizio ) {
		return null;
	}

	$fine = lidia_webinar_momento( $id, 'lidia_webinar_fine' );
	$a    = $fine ? $fine->getTimestamp() : $inizio->getTimestamp() + LIDIA_WEBINAR_DURATA;

	return array( $inizio->getTimestamp(), max( $a, $inizio->getTimestamp() ) );
}

/**
 * Il webinar è finito? Senza data non è mai finito.
 *
 * @param int $id ID del webinar.
 * @return bool
 */
function lidia_webinar_concluso( $id ) {
	$intervallo = lidia_webinar_intervallo( $id );

	return $intervallo ? time() > $intervallo[1] : false;
}

/**
 * Data e ora in chiaro: «giovedì 10 dicembre 2026, ore 17:00–18:00».
 *
 * @param int $id ID del webinar.
 * @return string Vuota se manca la data.
 */
function lidia_webinar_quando( $id ) {
	$inizio = lidia_webinar_momento( $id, 'lidia_webinar_inizio' );

	if ( ! $inizio ) {
		return '';
	}

	$fine  = lidia_webinar_momento( $id, 'lidia_webinar_fine' );
	$testo = wp_date( 'l j F Y', $inizio->getTimestamp() ) . ', ' . __( 'ore', 'lidia' ) . ' ' . wp_date( 'H:i', $inizio->getTimestamp() );

	if ( $fine && $fine > $inizio ) {
		$testo .= '–' . wp_date( 'H:i', $fine->getTimestamp() );
	}

	return $testo;
}

/**
 * La riga sotto il titolo: «Webinar · giovedì 10 dicembre 2026, ore 17:00–18:00 · Microsoft Teams».
 *
 * @param int $id ID del webinar.
 * @return string
 */
function lidia_webinar_riga( $id ) {
	$quando = lidia_webinar_quando( $id );
	$pezzi  = array( __( 'Webinar', 'lidia' ) );

	if ( '' !== $quando ) {
		$pezzi[] = $quando;
	}

	$pezzi[] = 'Microsoft Teams';

	return implode( ' · ', $pezzi );
}

/**
 * Al posto del modulo, a webinar finito.
 *
 * Con un webinar successivo indicato (pubblicato e non ancora finito): il rimando. Senza:
 * le iscrizioni sono chiuse. Testi approvati il 18/11/2026.
 *
 * @param int $id ID del webinar.
 * @return string
 */
function lidia_webinar_chiuso( $id ) {
	$prossimo = absint( get_post_meta( (int) $id, 'lidia_webinar_prossimo', true ) );
	$valido   = $prossimo && $prossimo !== (int) $id && 'webinar' === get_post_type( $prossimo ) && 'publish' === get_post_status( $prossimo ) && ! lidia_webinar_concluso( $prossimo );

	$html = '<div class="lidia-modulo-conferma lidia-webinar-chiuso" role="status"><p>';

	if ( $valido ) {
		$quando = lidia_webinar_quando( $prossimo );
		$etichetta = get_the_title( $prossimo ) . ( '' !== $quando ? ', ' . $quando : '' );

		$html .= esc_html__( 'Il webinar si è concluso. Il prossimo appuntamento:', 'lidia' ) . ' ';
		$html .= '<a href="' . esc_url( get_permalink( $prossimo ) ) . '">' . esc_html( $etichetta ) . ' →</a>';
	} else {
		$html .= esc_html__( 'Il webinar si è concluso. Le iscrizioni sono chiuse.', 'lidia' );
	}

	return $html . '</p></div>';
}

/**
 * Un pezzo della data per i block binding della pagina (24/11/2026).
 *
 * Chiavi: `riga` (la riga completa), `quando` («mercoledì 9 dicembre 2026, ore 14:00–15:00»),
 * `data` («mercoledì 9 dicembre 2026»), `orario` («14:00–15:00»). La data sta solo nei campi
 * del webinar: la pagina la legge da lì, non la ripete nel testo.
 *
 * @param int    $id    ID del webinar.
 * @param string $chiave riga, quando, data, orario.
 * @return string
 */
function lidia_webinar_valore( $id, $chiave ) {
	$inizio = lidia_webinar_momento( $id, 'lidia_webinar_inizio' );

	if ( 'quando' === $chiave ) {
		return lidia_webinar_quando( $id );
	}

	if ( 'data' === $chiave ) {
		return $inizio ? wp_date( 'l j F Y', $inizio->getTimestamp() ) : '';
	}

	if ( 'orario' === $chiave ) {
		if ( ! $inizio ) {
			return '';
		}

		$fine  = lidia_webinar_momento( $id, 'lidia_webinar_fine' );
		$testo = wp_date( 'H:i', $inizio->getTimestamp() );

		if ( $fine && $fine > $inizio ) {
			$testo .= '–' . wp_date( 'H:i', $fine->getTimestamp() );
		}

		return $testo;
	}

	return lidia_webinar_riga( $id );
}

/**
 * Data e orario arrivano nella pagina con un block binding: paragrafi normali, legati alla
 * sorgente `lidia/webinar` con la chiave che serve (`lidia_webinar_valore`). Niente blocco su
 * misura per una riga di testo.
 */
function lidia_webinar_registra_binding() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}

	register_block_bindings_source(
		'lidia/webinar',
		array(
			'label'              => __( 'Webinar', 'lidia' ),
			'uses_context'       => array( 'postId' ),
			'get_value_callback' => function ( $argomenti, $blocco ) {
				$id = ! empty( $blocco->context['postId'] ) ? (int) $blocco->context['postId'] : (int) get_the_ID();

				if ( ! $id || 'webinar' !== get_post_type( $id ) ) {
					return '';
				}

				return esc_html( lidia_webinar_valore( $id, isset( $argomenti['key'] ) ? (string) $argomenti['key'] : 'riga' ) );
			},
		)
	);
}
add_action( 'init', 'lidia_webinar_registra_binding' );

/* -------------------------------------------------------------------------
 * Microsoft Graph
 * ---------------------------------------------------------------------- */

/**
 * Le credenziali dell'app ci sono tutte?
 *
 * @return bool
 */
function lidia_teams_configurato() {
	foreach ( array( 'LIDIA_TEAMS_TENANT_ID', 'LIDIA_TEAMS_CLIENT_ID', 'LIDIA_TEAMS_CLIENT_SECRET' ) as $costante ) {
		if ( ! defined( $costante ) || '' === (string) constant( $costante ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Token applicativo (client credentials), in cache fino a cinque minuti prima della scadenza.
 *
 * @param bool $nuovo Ignora la cache.
 * @return string Vuoto se non si ottiene.
 */
function lidia_teams_token( $nuovo = false ) {
	if ( ! lidia_teams_configurato() ) {
		return '';
	}

	if ( ! $nuovo ) {
		$salvato = get_transient( 'lidia_teams_token' );

		if ( is_string( $salvato ) && '' !== $salvato ) {
			return $salvato;
		}
	}

	$risposta = wp_remote_post(
		'https://login.microsoftonline.com/' . rawurlencode( LIDIA_TEAMS_TENANT_ID ) . '/oauth2/v2.0/token',
		array(
			'timeout' => 10,
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => LIDIA_TEAMS_CLIENT_ID,
				'client_secret' => LIDIA_TEAMS_CLIENT_SECRET,
				'scope'         => 'https://graph.microsoft.com/.default',
			),
		)
	);

	if ( is_wp_error( $risposta ) ) {
		lidia_teams_log( 'token: ' . $risposta->get_error_code() );

		return '';
	}

	$codice = (int) wp_remote_retrieve_response_code( $risposta );
	$corpo  = json_decode( (string) wp_remote_retrieve_body( $risposta ), true );

	if ( 200 !== $codice || empty( $corpo['access_token'] ) ) {
		lidia_teams_log( 'token: HTTP ' . $codice . ' ' . ( isset( $corpo['error'] ) ? sanitize_text_field( (string) $corpo['error'] ) : '' ) );

		return '';
	}

	$durata = isset( $corpo['expires_in'] ) ? (int) $corpo['expires_in'] : 3600;

	set_transient( 'lidia_teams_token', (string) $corpo['access_token'], max( 60, $durata - 300 ) );

	return (string) $corpo['access_token'];
}

/**
 * Una chiamata a Graph. Su 401 rinnova il token e riprova una volta.
 *
 * @param string     $metodo   GET o POST.
 * @param string     $percorso Percorso dopo /v1.0, o URL completo (paginazione).
 * @param array|null $corpo    Corpo JSON.
 * @return array codice (0 = rete o token), corpo (array|null), errore (codice Graph o di rete).
 */
function lidia_teams_chiama( $metodo, $percorso, $corpo = null ) {
	$url = 0 === strpos( $percorso, 'https://' ) ? $percorso : 'https://graph.microsoft.com/v1.0' . $percorso;

	for ( $giro = 0; $giro < 2; $giro++ ) {
		$token = lidia_teams_token( $giro > 0 );

		if ( '' === $token ) {
			return array(
				'codice' => 0,
				'corpo'  => null,
				'errore' => 'token',
			);
		}

		$argomenti = array(
			'method'  => $metodo,
			'timeout' => 10,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
		);

		if ( null !== $corpo ) {
			$argomenti['body'] = wp_json_encode( $corpo );
		}

		$risposta = wp_remote_request( $url, $argomenti );

		if ( is_wp_error( $risposta ) ) {
			return array(
				'codice' => 0,
				'corpo'  => null,
				'errore' => $risposta->get_error_code(),
			);
		}

		$codice = (int) wp_remote_retrieve_response_code( $risposta );

		if ( 401 === $codice && 0 === $giro ) {
			delete_transient( 'lidia_teams_token' );
			continue;
		}

		$dati = json_decode( (string) wp_remote_retrieve_body( $risposta ), true );

		return array(
			'codice' => $codice,
			'corpo'  => is_array( $dati ) ? $dati : null,
			'errore' => isset( $dati['error']['code'] ) ? sanitize_text_field( (string) $dati['error']['code'] ) : '',
		);
	}

	return array(
		'codice' => 401,
		'corpo'  => null,
		'errore' => 'token',
	);
}

/**
 * Riga nel log degli errori PHP. Mai nomi, email o segreti: solo codici.
 *
 * @param string $testo Testo.
 */
function lidia_teams_log( $testo ) {
	error_log( '[lidia-teams] ' . $testo ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

/**
 * L'ID del webinar in un percorso Graph: contiene «@», che resta com'è.
 *
 * @param string $teams_id ID Teams.
 * @return string
 */
function lidia_teams_id_percorso( $teams_id ) {
	return str_replace( '%40', '@', rawurlencode( (string) $teams_id ) );
}

/**
 * Iscrive una persona a un webinar Teams.
 *
 * Con l'accesso applicativo Graph risponde 204 senza corpo. Un'email già iscritta non è un
 * errore: Microsoft non documenta la risposta, quindi si accettano il 409 e gli errori che
 * parlano di registrazione esistente.
 *
 * @param string $teams_id ID del webinar Teams.
 * @param array  $persona  nome, cognome, email.
 * @return array esito (ok · riprova · errore), codice, errore.
 */
function lidia_teams_iscrivi( $teams_id, $persona ) {
	$risultato = lidia_teams_chiama(
		'POST',
		'/solutions/virtualEvents/webinars/' . lidia_teams_id_percorso( $teams_id ) . '/registrations',
		array(
			'firstName'         => $persona['nome'],
			'lastName'          => $persona['cognome'],
			'email'             => $persona['email'],
			'preferredTimezone' => LIDIA_TEAMS_FUSO,
			'preferredLanguage' => LIDIA_TEAMS_LINGUA,
		)
	);

	$codice    = $risultato['codice'];
	$messaggio = isset( $risultato['corpo']['error']['message'] ) ? strtolower( (string) $risultato['corpo']['error']['message'] ) : '';

	if ( $codice >= 200 && $codice < 300 ) {
		$esito = 'ok';
	} elseif ( 409 === $codice || ( 400 === $codice && false !== strpos( $messaggio, 'already' ) ) ) {
		$esito = 'ok';
	} elseif ( 0 === $codice || 429 === $codice || $codice >= 500 ) {
		$esito = 'riprova';
	} else {
		$esito = 'errore';
	}

	if ( 'ok' !== $esito || $codice >= 300 ) {
		lidia_teams_log( 'iscrizione: HTTP ' . $codice . ' ' . $risultato['errore'] . ' → ' . $esito );
	}

	return array(
		'esito'  => $esito,
		'codice' => $codice,
		'errore' => $risultato['errore'],
	);
}

/* -------------------------------------------------------------------------
 * Coda di riprove e allarme (stesso schema di inc/delera.php)
 * ---------------------------------------------------------------------- */

/**
 * Mette un'iscrizione in coda e programma la riprova.
 *
 * @param array $voce      teams_id, webinar (ID post), nome, cognome, email.
 * @param int   $tentativi Tentativi già fatti.
 */
function lidia_teams_accoda( $voce, $tentativi ) {
	$coda = get_option( 'lidia_teams_coda', array() );

	if ( ! is_array( $coda ) ) {
		$coda = array();
	}

	if ( count( $coda ) >= LIDIA_DELERA_CODA_MAX ) {
		lidia_teams_allarme( $voce, __( 'coda piena', 'lidia' ) );

		return;
	}

	$coda[] = array(
		'voce'      => $voce,
		'tentativi' => (int) $tentativi,
		'nata'      => time(),
	);

	update_option( 'lidia_teams_coda', $coda, false );

	$attese = LIDIA_DELERA_ATTESE;
	$attesa = isset( $attese[ $tentativi ] ) ? $attese[ $tentativi ] : end( $attese );

	if ( ! wp_next_scheduled( 'lidia_teams_riprova' ) ) {
		wp_schedule_single_event( time() + $attesa, 'lidia_teams_riprova' );
	}
}

/**
 * Riprova la coda. Quello che non passa torna in coda, o esce per posta.
 */
function lidia_teams_riprova() {
	$coda = get_option( 'lidia_teams_coda', array() );

	if ( ! is_array( $coda ) || ! $coda ) {
		return;
	}

	update_option( 'lidia_teams_coda', array(), false );

	foreach ( $coda as $elemento ) {
		$voce      = isset( $elemento['voce'] ) ? $elemento['voce'] : array();
		$tentativi = isset( $elemento['tentativi'] ) ? (int) $elemento['tentativi'] : 0;
		$nata      = isset( $elemento['nata'] ) ? (int) $elemento['nata'] : time();

		if ( empty( $voce['teams_id'] ) ) {
			continue;
		}

		if ( time() - $nata > LIDIA_DELERA_CODA_VITA ) {
			lidia_teams_allarme( $voce, __( 'scaduta in coda', 'lidia' ) );
			continue;
		}

		$esito = lidia_teams_iscrivi( $voce['teams_id'], $voce );

		if ( 'ok' === $esito['esito'] ) {
			continue;
		}

		if ( 'errore' === $esito['esito'] ) {
			lidia_teams_allarme( $voce, 'HTTP ' . $esito['codice'] . ' ' . $esito['errore'] );
			continue;
		}

		++$tentativi;

		if ( $tentativi >= count( LIDIA_DELERA_ATTESE ) ) {
			lidia_teams_allarme( $voce, __( 'tre tentativi falliti', 'lidia' ) );
			continue;
		}

		lidia_teams_accoda( $voce, $tentativi );
	}
}
add_action( 'lidia_teams_riprova', 'lidia_teams_riprova' );

/**
 * L'iscrizione non è arrivata su Teams: va fatta a mano. Mail a LIDIA_FORM_ALERT_EMAIL.
 *
 * @param array  $voce   teams_id, webinar, nome, cognome, email.
 * @param string $motivo Perché.
 */
function lidia_teams_allarme( $voce, $motivo ) {
	$a = defined( 'LIDIA_FORM_ALERT_EMAIL' ) ? (string) LIDIA_FORM_ALERT_EMAIL : get_option( 'admin_email' );

	if ( ! $a ) {
		return;
	}

	$titolo = ! empty( $voce['webinar'] ) ? get_the_title( (int) $voce['webinar'] ) : '';

	$righe = array(
		sprintf( /* translators: %s: motivo. */ __( 'Questa iscrizione non è arrivata su Microsoft Teams (%s). Va fatta a mano dal webinar in Teams.', 'lidia' ), $motivo ),
		'',
		'webinar: ' . $titolo,
		'nome: ' . ( isset( $voce['nome'] ) ? $voce['nome'] : '' ),
		'cognome: ' . ( isset( $voce['cognome'] ) ? $voce['cognome'] : '' ),
		'email: ' . ( isset( $voce['email'] ) ? $voce['email'] : '' ),
	);

	wp_mail( $a, __( '[Lidia] Un’iscrizione al webinar non è arrivata su Teams', 'lidia' ), implode( "\n", $righe ) );
}

/* -------------------------------------------------------------------------
 * L'endpoint chiamato da Delera
 * ---------------------------------------------------------------------- */

/**
 * Il token condiviso con Delera: intestazione X-Lidia-Token, o campo `token` nel corpo se il
 * webhook di Delera non permette intestazioni personalizzate.
 *
 * @param WP_REST_Request $richiesta Richiesta.
 * @return true|WP_Error
 */
function lidia_webinar_autorizza( $richiesta ) {
	if ( ! defined( 'LIDIA_WEBINAR_TOKEN' ) || '' === (string) LIDIA_WEBINAR_TOKEN ) {
		return new WP_Error( 'lidia_non_configurato', 'Endpoint non configurato.', array( 'status' => 503 ) );
	}

	$dato = (string) $richiesta->get_header( 'x-lidia-token' );

	if ( '' === $dato ) {
		$dato = (string) $richiesta->get_param( 'token' );
	}

	if ( '' === $dato || ! hash_equals( (string) LIDIA_WEBINAR_TOKEN, $dato ) ) {
		return new WP_Error( 'lidia_non_autorizzato', 'Token non valido.', array( 'status' => 401 ) );
	}

	return true;
}

/**
 * Risposta JSON breve.
 *
 * @param int    $stato   Codice HTTP.
 * @param string $esito   Parola chiave per il workflow Delera.
 * @return WP_REST_Response
 */
function lidia_webinar_risposta( $stato, $esito ) {
	return new WP_REST_Response(
		array(
			'ok'    => $stato < 300,
			'esito' => $esito,
		),
		$stato
	);
}

/**
 * POST /wp-json/lidia/v1/webinar-registration
 *
 * Corpo atteso: first_name, last_name, email, webinar_slug (si accettano anche nome, cognome,
 * webinar). Risposte: 200 iscritto (o già iscritto) · 202 in coda per riprova · 400 dati
 * mancanti · 401 token · 404 webinar inesistente · 410 webinar concluso · 502 Teams ha
 * rifiutato · 503 credenziali mancanti.
 *
 * @param WP_REST_Request $richiesta Richiesta.
 * @return WP_REST_Response
 */
function lidia_webinar_rest_iscrizione( $richiesta ) {
	$prendi = static function ( $chiavi ) use ( $richiesta ) {
		foreach ( $chiavi as $chiave ) {
			$valore = $richiesta->get_param( $chiave );

			if ( is_scalar( $valore ) && '' !== trim( (string) $valore ) ) {
				return trim( sanitize_text_field( (string) $valore ) );
			}
		}

		return '';
	};

	$persona = array(
		'nome'    => substr( $prendi( array( 'first_name', 'nome' ) ), 0, 80 ),
		'cognome' => substr( $prendi( array( 'last_name', 'cognome' ) ), 0, 80 ),
		'email'   => sanitize_email( $prendi( array( 'email' ) ) ),
	);

	if ( '' === $persona['nome'] || '' === $persona['cognome'] || ! is_email( $persona['email'] ) ) {
		return lidia_webinar_risposta( 400, 'dati-mancanti' );
	}

	$webinar = lidia_webinar_da_slug( $prendi( array( 'webinar_slug', 'webinar' ) ) );

	if ( ! $webinar ) {
		return lidia_webinar_risposta( 404, 'webinar-inesistente' );
	}

	if ( lidia_webinar_concluso( $webinar->ID ) ) {
		return lidia_webinar_risposta( 410, 'webinar-concluso' );
	}

	$teams_id = (string) get_post_meta( $webinar->ID, 'lidia_webinar_teams_id', true );

	if ( '' === $teams_id || ! lidia_teams_configurato() ) {
		lidia_teams_log( 'configurazione mancante per il webinar ' . $webinar->ID );

		return lidia_webinar_risposta( 503, 'non-configurato' );
	}

	$voce  = $persona + array(
		'teams_id' => $teams_id,
		'webinar'  => (int) $webinar->ID,
	);
	$esito = lidia_teams_iscrivi( $teams_id, $persona );

	if ( 'ok' === $esito['esito'] ) {
		return lidia_webinar_risposta( 200, 'iscritto' );
	}

	if ( 'riprova' === $esito['esito'] ) {
		lidia_teams_accoda( $voce, 0 );

		return lidia_webinar_risposta( 202, 'in-coda' );
	}

	lidia_teams_allarme( $voce, 'HTTP ' . $esito['codice'] . ' ' . $esito['errore'] );

	return lidia_webinar_risposta( 502, 'rifiutato-da-teams' );
}

/** Registra la rotta. */
function lidia_webinar_rotta() {
	register_rest_route(
		'lidia/v1',
		'/webinar-registration',
		array(
			'methods'             => 'POST',
			'callback'            => 'lidia_webinar_rest_iscrizione',
			'permission_callback' => 'lidia_webinar_autorizza',
		)
	);
}
add_action( 'rest_api_init', 'lidia_webinar_rotta' );

/* -------------------------------------------------------------------------
 * Scadenza del client secret: avviso in admin 30 giorni prima
 * ---------------------------------------------------------------------- */

/** Avviso agli amministratori quando il secret scade entro 30 giorni (o è scaduto). */
function lidia_teams_avviso_scadenza() {
	if ( ! current_user_can( 'manage_options' ) || ! defined( 'LIDIA_TEAMS_SECRET_SCADENZA' ) ) {
		return;
	}

	$scadenza = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) LIDIA_TEAMS_SECRET_SCADENZA, wp_timezone() );

	if ( ! $scadenza ) {
		return;
	}

	$giorni = (int) floor( ( $scadenza->getTimestamp() - time() ) / DAY_IN_SECONDS );

	if ( $giorni > LIDIA_TEAMS_PREAVVISO ) {
		return;
	}

	$data  = wp_date( 'j F Y', $scadenza->getTimestamp() );
	$testo = $giorni < 0
		? sprintf( /* translators: %s: data. */ __( 'Il client secret dell’app Teams è scaduto il %s: le iscrizioni ai webinar non arrivano più su Teams.', 'lidia' ), $data )
		: sprintf( /* translators: %s: data. */ __( 'Il client secret dell’app Teams scade il %s.', 'lidia' ), $data );

	$testo .= ' ' . __( 'Va rinnovato in Entra ID, poi aggiornati LIDIA_TEAMS_CLIENT_SECRET e LIDIA_TEAMS_SECRET_SCADENZA in wp-config.php (scripts/30-webinar.md).', 'lidia' );

	printf( '<div class="notice notice-%s"><p>%s</p></div>', $giorni < 0 ? 'error' : 'warning', esc_html( $testo ) );
}
add_action( 'admin_notices', 'lidia_teams_avviso_scadenza' );

/* -------------------------------------------------------------------------
 * WP-CLI: elenco dei webinar dell'organizzatore e iscrizione di prova
 * ---------------------------------------------------------------------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Webinar Teams di Lidia.
	 */
	class Lidia_Webinar_CLI {

		/**
		 * Elenca i webinar dell'organizzatore: titolo, inizio, stato, ID da mettere nella pagina.
		 *
		 * ## OPTIONS
		 *
		 * [--utente=<id>]
		 * : ID Entra dell'organizzatore. Default: LIDIA_TEAMS_ORGANIZZATORE.
		 *
		 * ## EXAMPLES
		 *
		 *     wp lidia webinar elenco
		 *
		 * @param array $args       Argomenti.
		 * @param array $assoc_args Opzioni.
		 */
		public function elenco( $args, $assoc_args ) {
			$utente = isset( $assoc_args['utente'] ) ? (string) $assoc_args['utente'] : ( defined( 'LIDIA_TEAMS_ORGANIZZATORE' ) ? (string) LIDIA_TEAMS_ORGANIZZATORE : '' );

			if ( '' === $utente ) {
				WP_CLI::error( 'Manca l’organizzatore: --utente=<id> o LIDIA_TEAMS_ORGANIZZATORE in wp-config.php.' );
			}

			$percorso = "/solutions/virtualEvents/webinars/getByUserIdAndRole(userId='" . rawurlencode( $utente ) . "',role='organizer')";
			$righe    = array();

			while ( $percorso ) {
				$risultato = lidia_teams_chiama( 'GET', $percorso );

				if ( 200 !== $risultato['codice'] ) {
					WP_CLI::error( 'Graph: HTTP ' . $risultato['codice'] . ' ' . $risultato['errore'] );
				}

				foreach ( (array) ( isset( $risultato['corpo']['value'] ) ? $risultato['corpo']['value'] : array() ) as $w ) {
					$righe[] = array(
						'titolo' => isset( $w['displayName'] ) ? $w['displayName'] : '',
						'inizio' => isset( $w['startDateTime']['dateTime'] ) ? substr( $w['startDateTime']['dateTime'], 0, 16 ) . ' ' . ( isset( $w['startDateTime']['timeZone'] ) ? $w['startDateTime']['timeZone'] : '' ) : '',
						'stato'  => isset( $w['status'] ) ? $w['status'] : '',
						'id'     => isset( $w['id'] ) ? $w['id'] : '',
					);
				}

				$percorso = isset( $risultato['corpo']['@odata.nextLink'] ) ? (string) $risultato['corpo']['@odata.nextLink'] : '';
			}

			if ( ! $righe ) {
				WP_CLI::warning( 'Nessun webinar per questo organizzatore.' );

				return;
			}

			WP_CLI\Utils\format_items( 'table', $righe, array( 'titolo', 'inizio', 'stato', 'id' ) );
		}

		/**
		 * Iscrive una persona al webinar di una pagina, senza passare da Delera. Per la prova.
		 *
		 * ## OPTIONS
		 *
		 * --slug=<slug>
		 * : Slug della pagina webinar.
		 *
		 * --nome=<nome>
		 * : Nome della persona da iscrivere.
		 *
		 * --cognome=<cognome>
		 * : Cognome della persona da iscrivere.
		 *
		 * --email=<email>
		 * : Email della persona da iscrivere (per la prova: un indirizzo esterno).
		 *
		 * ## EXAMPLES
		 *
		 *     wp lidia webinar iscrivi --slug=nome-webinar --nome=Mario --cognome=Rossi --email=mario@example.com
		 *
		 * @param array $args       Argomenti.
		 * @param array $assoc_args Opzioni.
		 */
		public function iscrivi( $args, $assoc_args ) {
			$webinar = lidia_webinar_da_slug( isset( $assoc_args['slug'] ) ? $assoc_args['slug'] : '' );

			if ( ! $webinar ) {
				WP_CLI::error( 'Nessun webinar pubblicato con questo slug.' );
			}

			$teams_id = (string) get_post_meta( $webinar->ID, 'lidia_webinar_teams_id', true );

			if ( '' === $teams_id ) {
				WP_CLI::error( 'La pagina non ha l’ID del webinar Teams (lidia_webinar_teams_id).' );
			}

			$esito = lidia_teams_iscrivi(
				$teams_id,
				array(
					'nome'    => (string) $assoc_args['nome'],
					'cognome' => (string) $assoc_args['cognome'],
					'email'   => sanitize_email( (string) $assoc_args['email'] ),
				)
			);

			if ( 'ok' === $esito['esito'] ) {
				WP_CLI::success( 'Iscritto (HTTP ' . $esito['codice'] . '). Teams manda la mail con il link.' );

				return;
			}

			WP_CLI::error( 'Graph: HTTP ' . $esito['codice'] . ' ' . $esito['errore'] . ' (' . $esito['esito'] . ')' );
		}
	}

	WP_CLI::add_command( 'lidia webinar', 'Lidia_Webinar_CLI' );
}
