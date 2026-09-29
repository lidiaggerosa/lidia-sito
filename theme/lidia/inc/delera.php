<?php
/**
 * Consegna a Delera: webhook, riprove, coda, allarme. E il download firmato dei whitepaper.
 *
 * Il sito non è un archivio di lead: qui non c'è nessun registro dei contatti, solo una coda
 * che per definizione si svuota. Specifica: docs/11-form-delera.md §4 e §5.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/** Attese fra una riprova e l'altra, in secondi. */
const LIDIA_DELERA_ATTESE = array( 300, 1800, 10800 );

/** Quante richieste tiene la coda, al massimo. */
const LIDIA_DELERA_CODA_MAX = 200;

/** Dopo quanto una richiesta in coda si abbandona. */
const LIDIA_DELERA_CODA_VITA = 7 * DAY_IN_SECONDS;

/** Nome della cartella dei PDF protetti dal modulo (accanto alla radice web, o in uploads/ come ripiego). */
const LIDIA_CARTELLA_RISERVATA = 'riservati';

/**
 * L'indirizzo del webhook per questo tipo di modulo.
 *
 * Le URL stanno in wp-config.php: non entrano mai nei file di progetto.
 *
 * @param string $tipo prova o whitepaper.
 * @return string
 */
function lidia_delera_url( $tipo ) {
	$costante = 'whitepaper' === $tipo ? 'LIDIA_DELERA_WEBHOOK_WHITEPAPER' : 'LIDIA_DELERA_WEBHOOK_PROVA';

	return defined( $costante ) ? (string) constant( $costante ) : '';
}

/**
 * Il payload, dai dati normalizzati del modulo.
 *
 * @param array $dati Dati del modulo.
 * @return array
 */
function lidia_delera_payload( $dati ) {
	$adesso    = current_time( 'c' );
	$documento = array();

	if ( 'whitepaper' === $dati['tipo'] && $dati['documento'] ) {
		$post = get_post( $dati['documento'] );

		if ( $post ) {
			$documento = array(
				'id'     => (int) $post->ID,
				'slug'   => $post->post_name,
				'titolo' => get_the_title( $post ),
				'tag'    => (string) get_post_meta( $post->ID, 'lidia_delera_tag', true ),
			);
		}
	}

	$professioni = lidia_modulo_professioni();

	return array(
		'tipo'               => $dati['tipo'],
		'intento'            => $dati['intento'],
		'nome'               => $dati['nome'],
		'cognome'            => $dati['cognome'],
		'email'              => $dati['email'],
		'telefono'           => $dati['telefono'],
		'azienda'            => $dati['azienda'],
		'professione'        => isset( $professioni[ $dati['professione'] ] ) ? $professioni[ $dati['professione'] ] : $dati['professione'],
		'professione_chiave' => $dati['professione'],
		'consenso_privacy'   => array(
			'dato'   => (bool) $dati['consenso_privacy'],
			'quando' => $adesso,
			'testo'  => LIDIA_MODULO_CONSENSO,
		),
		'consenso_marketing' => array(
			'dato'   => (bool) $dati['consenso_marketing'],
			'quando' => $adesso,
			'testo'  => LIDIA_MODULO_CONSENSO,
		),
		'origine'            => $dati['origine'],
		'posizione'          => isset( $dati['posizione'] ) ? $dati['posizione'] : '',
		'campagna'           => isset( $dati['campagna'] ) ? $dati['campagna'] : array(),
		'documento'          => $documento,
	);
}

/**
 * Prova a consegnare. Vero se Delera ha risposto bene.
 *
 * @param array $payload Payload.
 * @return bool
 */
function lidia_delera_invia( $payload ) {
	$url = lidia_delera_url( isset( $payload['tipo'] ) ? $payload['tipo'] : 'prova' );

	if ( '' === $url ) {
		return false;
	}

	$risposta = wp_remote_post(
		$url,
		array(
			'timeout' => 8,
			'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'    => wp_json_encode( $payload ),
		)
	);

	if ( is_wp_error( $risposta ) ) {
		return false;
	}

	$codice = (int) wp_remote_retrieve_response_code( $risposta );

	return $codice >= 200 && $codice < 300;
}

/**
 * Consegna subito; se fallisce, mette in coda.
 *
 * @param array $dati Dati del modulo.
 * @return bool Consegnato al primo colpo.
 */
function lidia_delera_consegna( $dati ) {
	$payload = lidia_delera_payload( $dati );

	if ( lidia_delera_invia( $payload ) ) {
		return true;
	}

	lidia_delera_accoda( $payload, 0 );

	return false;
}

/**
 * Mette una richiesta in coda e programma la riprova.
 *
 * @param array $payload   Payload.
 * @param int   $tentativi Tentativi già fatti.
 */
function lidia_delera_accoda( $payload, $tentativi ) {
	$coda = get_option( 'lidia_delera_coda', array() );

	if ( ! is_array( $coda ) ) {
		$coda = array();
	}

	if ( count( $coda ) >= LIDIA_DELERA_CODA_MAX ) {
		// La coda è piena: questa richiesta non si perde, va direttamente per posta.
		lidia_delera_allarme( $payload, __( 'coda piena', 'lidia' ) );

		return;
	}

	$coda[] = array(
		'payload'   => $payload,
		'tentativi' => (int) $tentativi,
		'nata'      => time(),
	);

	update_option( 'lidia_delera_coda', $coda, false );

	$attese = LIDIA_DELERA_ATTESE;
	$attesa = isset( $attese[ $tentativi ] ) ? $attese[ $tentativi ] : end( $attese );

	if ( ! wp_next_scheduled( 'lidia_delera_riprova' ) ) {
		wp_schedule_single_event( time() + $attesa, 'lidia_delera_riprova' );
	}
}

/**
 * Riprova tutta la coda. Quello che non passa torna in coda, o esce per posta.
 */
function lidia_delera_riprova() {
	$coda = get_option( 'lidia_delera_coda', array() );

	if ( ! is_array( $coda ) || ! $coda ) {
		return;
	}

	update_option( 'lidia_delera_coda', array(), false );

	foreach ( $coda as $voce ) {
		$payload   = isset( $voce['payload'] ) ? $voce['payload'] : array();
		$tentativi = isset( $voce['tentativi'] ) ? (int) $voce['tentativi'] : 0;
		$nata      = isset( $voce['nata'] ) ? (int) $voce['nata'] : time();

		if ( ! $payload ) {
			continue;
		}

		if ( time() - $nata > LIDIA_DELERA_CODA_VITA ) {
			lidia_delera_allarme( $payload, __( 'scaduta in coda', 'lidia' ) );
			continue;
		}

		if ( lidia_delera_invia( $payload ) ) {
			continue;
		}

		++$tentativi;

		if ( $tentativi >= count( LIDIA_DELERA_ATTESE ) ) {
			lidia_delera_allarme( $payload, __( 'tre tentativi falliti', 'lidia' ) );
			continue;
		}

		lidia_delera_accoda( $payload, $tentativi );
	}
}
add_action( 'lidia_delera_riprova', 'lidia_delera_riprova' );

/**
 * Quando Delera non è raggiungibile, il lead arriva per posta. Non si perde mai.
 *
 * @param array  $payload Payload.
 * @param string $motivo  Perché siamo qui.
 */
function lidia_delera_allarme( $payload, $motivo ) {
	$a = defined( 'LIDIA_FORM_ALERT_EMAIL' ) ? (string) LIDIA_FORM_ALERT_EMAIL : get_option( 'admin_email' );

	if ( ! $a ) {
		return;
	}

	$righe = array(
		sprintf( /* translators: %s: motivo. */ __( 'Delera non ha ricevuto questa richiesta (%s). I dati sono qui sotto.', 'lidia' ), $motivo ),
		'',
	);

	foreach ( array( 'tipo', 'intento', 'nome', 'cognome', 'email', 'telefono', 'azienda', 'professione', 'origine', 'posizione' ) as $chiave ) {
		if ( ! empty( $payload[ $chiave ] ) ) {
			$righe[] = $chiave . ': ' . $payload[ $chiave ];
		}
	}

	if ( ! empty( $payload['documento']['titolo'] ) ) {
		$righe[] = 'documento: ' . $payload['documento']['titolo'];
	}

	if ( ! empty( $payload['campagna'] ) && is_array( $payload['campagna'] ) ) {
		foreach ( $payload['campagna'] as $chiave => $valore ) {
			$righe[] = $chiave . ': ' . $valore;
		}
	}

	$righe[] = 'consenso marketing: ' . ( ! empty( $payload['consenso_marketing']['dato'] ) ? 'sì' : 'no' );

	wp_mail(
		$a,
		__( '[Lidia] Una richiesta non è arrivata in Delera', 'lidia' ),
		implode( "\n", $righe )
	);
}

/* -------------------------------------------------------------------------
 * I PDF protetti stanno fuori dalla radice web
 *
 * Un file in uploads/ è pubblico per costruzione: chi ne conosce l'indirizzo lo scarica
 * senza modulo. Un .htaccess nella cartella non basta: su SiteGround i file statici li
 * serve nginx, che il .htaccess non lo legge (verificato il 23/09/2026 sullo staging).
 * I PDF caricati dalla pagina di un whitepaper finiscono quindi in una cartella accanto
 * alla radice del sito, che nessun server web può servire. L'unica strada per il file è
 * il link firmato, letto da PHP.
 *
 * Il percorso si può forzare con LIDIA_RISERVATI_PATH in wp-config.php. Se la cartella
 * non è scrivibile si torna a uploads/riservati/ con .htaccess, e l'editor lo segnala.
 * ---------------------------------------------------------------------- */

/**
 * La cartella dei PDF protetti, assoluta e senza slash finale.
 *
 * @return string
 */
function lidia_delera_cartella_riservata_percorso() {
	if ( defined( 'LIDIA_RISERVATI_PATH' ) && LIDIA_RISERVATI_PATH ) {
		return untrailingslashit( wp_normalize_path( LIDIA_RISERVATI_PATH ) );
	}

	return untrailingslashit( wp_normalize_path( dirname( ABSPATH ) ) ) . '/' . LIDIA_CARTELLA_RISERVATA;
}

/**
 * La cartella riservata esiste ed è scrivibile? Se manca, prova a crearla.
 *
 * @return bool
 */
function lidia_delera_cartella_riservata_pronta() {
	$percorso = lidia_delera_cartella_riservata_percorso();

	if ( ! is_dir( $percorso ) ) {
		wp_mkdir_p( $percorso );
	}

	if ( ! is_dir( $percorso ) || ! wp_is_writable( $percorso ) ) {
		return false;
	}

	$indice = trailingslashit( $percorso ) . 'index.html';

	if ( ! file_exists( $indice ) ) {
		file_put_contents( $indice, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	return true;
}

/**
 * L'ID del contenuto a cui si sta allegando il file, se è un whitepaper.
 *
 * @return int
 */
function lidia_delera_post_in_caricamento() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing — il caricamento è già autorizzato da WordPress.
	// `post_id` arriva dal caricatore classico, `post` dall'editor a blocchi (REST /wp/v2/media).
	$id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0;

	if ( ! $id && isset( $_REQUEST['post'] ) ) {
		$id = absint( $_REQUEST['post'] );
	}
	// phpcs:enable

	if ( ! $id || 'risorsa' !== get_post_type( $id ) ) {
		return 0;
	}

	return $id;
}

/**
 * Chiude una cartella dentro uploads/ ad Apache, se non lo è già. È il ripiego.
 *
 * @param string $percorso Cartella assoluta.
 */
function lidia_delera_chiudi_cartella( $percorso ) {
	if ( ! is_dir( $percorso ) ) {
		wp_mkdir_p( $percorso );
	}

	$htaccess = trailingslashit( $percorso ) . '.htaccess';

	if ( file_exists( $htaccess ) ) {
		return;
	}

	$regole = "# Cartella dei documenti protetti dal modulo: si servono solo dal link firmato.\n"
		. "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n"
		. "<IfModule !mod_authz_core.c>\n\tOrder deny,allow\n\tDeny from all\n</IfModule>\n";

	file_put_contents( $htaccess, $regole ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	file_put_contents( trailingslashit( $percorso ) . 'index.html', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
}

/**
 * I file allegati a un whitepaper vanno nella cartella riservata.
 *
 * `basedir` e `path` puntano fuori dalla radice web: WordPress salva in `_wp_attached_file`
 * il solo nome del file, e `lidia_delera_percorso_allegato()` lo ritrova. L'URL punta a un
 * indirizzo che non esiste: quei file non devono avere un indirizzo.
 *
 * @param array $dir Cartella di caricamento.
 * @return array
 */
function lidia_delera_cartella_riservata( $dir ) {
	if ( ! lidia_delera_post_in_caricamento() ) {
		return $dir;
	}

	if ( lidia_delera_cartella_riservata_pronta() ) {
		$percorso = lidia_delera_cartella_riservata_percorso();

		$dir['subdir']  = '';
		$dir['basedir'] = $percorso;
		$dir['path']    = $percorso;
		$dir['baseurl'] = $dir['baseurl'] . '/' . LIDIA_CARTELLA_RISERVATA;
		$dir['url']     = $dir['baseurl'];

		return $dir;
	}

	// Ripiego: dentro uploads/, chiusa ad Apache. Su nginx non basta, e l'editor lo dice.
	$dir['subdir'] = '/' . LIDIA_CARTELLA_RISERVATA;
	$dir['path']   = $dir['basedir'] . $dir['subdir'];
	$dir['url']    = $dir['baseurl'] . $dir['subdir'];

	lidia_delera_chiudi_cartella( $dir['path'] );

	return $dir;
}
add_filter( 'upload_dir', 'lidia_delera_cartella_riservata' );

/**
 * Ritrova un allegato salvato nella cartella riservata.
 *
 * Fuori dalla richiesta di caricamento il filtro `upload_dir` non è attivo, e WordPress
 * cerca il file dentro uploads/. Se lì non c'è ma c'è nella cartella riservata, è lui.
 *
 * @param string $percorso Percorso calcolato da WordPress.
 * @param int    $allegato ID dell'allegato.
 * @return string
 */
function lidia_delera_percorso_allegato( $percorso, $allegato ) {
	if ( ! $percorso || file_exists( $percorso ) ) {
		return $percorso;
	}

	$relativo = (string) get_post_meta( $allegato, '_wp_attached_file', true );

	if ( '' === $relativo || false !== strpos( $relativo, '..' ) ) {
		return $percorso;
	}

	$riservato = lidia_delera_cartella_riservata_percorso() . '/' . ltrim( $relativo, '/' );

	return file_exists( $riservato ) ? $riservato : $percorso;
}
add_filter( 'get_attached_file', 'lidia_delera_percorso_allegato', 10, 2 );

/**
 * Il file di un whitepaper sta in una cartella che il web server non serve?
 *
 * Vero solo per la cartella fuori dalla radice web. Il ripiego in uploads/riservati/
 * conta come pubblico: su nginx lo è.
 *
 * @param int $allegato ID dell'allegato.
 * @return bool
 */
function lidia_delera_file_riservato( $allegato ) {
	$percorso = get_attached_file( $allegato );

	if ( ! $percorso ) {
		return false;
	}

	return 0 === strpos( wp_normalize_path( $percorso ), lidia_delera_cartella_riservata_percorso() . '/' );
}

/* -------------------------------------------------------------------------
 * Download firmato del whitepaper
 * ---------------------------------------------------------------------- */

/**
 * Link al PDF, valido 24 ore.
 *
 * @param int $documento ID del whitepaper.
 * @return string
 */
function lidia_delera_link_download( $documento ) {
	$documento = (int) $documento;
	$scadenza  = time() + DAY_IN_SECONDS;

	return add_query_arg(
		array(
			'lidia_dl' => $documento,
			's'        => $scadenza,
			'k'        => lidia_delera_firma_download( $documento, $scadenza ),
		),
		home_url( '/' )
	);
}

/**
 * La firma del link.
 *
 * @param int $documento ID.
 * @param int $scadenza  Scadenza.
 * @return string
 */
function lidia_delera_firma_download( $documento, $scadenza ) {
	return hash_hmac( 'sha256', $documento . '|' . $scadenza, wp_salt( 'lidia-download' ) );
}

/**
 * Serve il PDF quando il link è valido.
 */
function lidia_delera_servi_download() {
	if ( empty( $_GET['lidia_dl'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended — il link è firmato.
	$documento = absint( $_GET['lidia_dl'] );
	$scadenza  = isset( $_GET['s'] ) ? absint( $_GET['s'] ) : 0;
	$firma     = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
	// phpcs:enable

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	nocache_headers();

	if ( ! $documento || $scadenza < time() || ! hash_equals( lidia_delera_firma_download( $documento, $scadenza ), $firma ) ) {
		wp_die( esc_html__( 'Questo link non è più valido. Compilate di nuovo il modulo sulla pagina del documento.', 'lidia' ), 403 );
	}

	// Solo whitepaper pubblicati: una bozza non si scarica, nemmeno con un link firmato.
	if ( ! lidia_modulo_documento_valido( $documento ) ) {
		wp_die( esc_html__( 'Il documento non è ancora disponibile. Scriveteci a lidia@lidiatech.ai e ve lo mandiamo.', 'lidia' ), 404 );
	}

	$allegato = (int) get_post_meta( $documento, 'lidia_file', true );
	$percorso = $allegato ? get_attached_file( $allegato ) : '';

	if ( ! $percorso || ! file_exists( $percorso ) ) {
		wp_die( esc_html__( 'Il documento non è ancora disponibile. Scriveteci a lidia@lidiatech.ai e ve lo mandiamo.', 'lidia' ), 404 );
	}

	header( 'Content-Type: ' . get_post_mime_type( $allegato ) );
	header( 'Content-Disposition: attachment; filename="' . basename( $percorso ) . '"' );
	header( 'Content-Length: ' . filesize( $percorso ) );

	readfile( $percorso ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'template_redirect', 'lidia_delera_servi_download' );

/**
 * Avviso nell'editor se il PDF di un whitepaper protetto sta in una cartella pubblica.
 *
 * Succede quando il file è stato caricato dalla Libreria media invece che dalla pagina
 * del whitepaper. Non si blocca niente: si dice dove sta il problema.
 */
function lidia_delera_avviso_file_pubblico() {
	$schermo = get_current_screen();

	if ( ! $schermo || 'risorsa' !== $schermo->post_type || 'post' !== $schermo->base ) {
		return;
	}

	$id       = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$allegato = $id ? (int) get_post_meta( $id, 'lidia_file', true ) : 0;

	if ( ! $allegato || lidia_delera_file_riservato( $allegato ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Il PDF di questo whitepaper è in una cartella pubblica: chi ne conosce l’indirizzo lo scarica senza modulo. Caricatelo di nuovo dal pannello «Documento protetto» di questa pagina.', 'lidia' )
	);
}
add_action( 'admin_notices', 'lidia_delera_avviso_file_pubblico' );
