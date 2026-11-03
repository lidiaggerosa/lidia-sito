<?php
/**
 * Il modulo di contatto: blocco, markup, validazione, antispam, ricezione.
 *
 * Form nativo del tema. Nessun iframe, nessuno script di terza parte, nessun cookie tecnico: il
 * modulo si vede sempre, anche con tutti i consensi rifiutati. L'unico cookie che il modulo
 * legge è quello di attribuzione (primo, intermedi, ultimo contatto), scritto dal browser solo
 * con il consenso marketing di Complianz. Senza consenso arriva solo il contatto della visita.
 * La consegna a Delera sta in inc/delera.php, il disegno in assets/css/sezioni/modulo.css.
 *
 * Il modulo convive con la cache di pagina (SiteGround). Tutto ciò che cambia da un
 * visitatore all'altro — marca temporale, URL di partenza, contatto della visita — lo scrive
 * il browser, non il server: l'HTML in cache è uguale per tutti e resta valido.
 *
 * Specifica: docs/11-form-delera.md
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/** Versione dei testi di consenso: cambia quando cambia il testo accettato. */
const LIDIA_MODULO_CONSENSO = 'v1';

/** Secondi minimi fra apertura e invio: sotto questa soglia è un bot. */
const LIDIA_MODULO_ATTESA = 3;

/**
 * Secondi massimi di vita di una marca. Sette giorni: la marca stampata nell'HTML può restare
 * in cache a lungo, e chi ha JavaScript spento deve poter inviare comunque. Con JavaScript la
 * marca viene rinnovata al primo tocco sul modulo, e lì vale il limite dei 3 secondi.
 */
const LIDIA_MODULO_SCADENZA = 7 * DAY_IN_SECONDS;

/** Invii all'ora per connessione. */
const LIDIA_MODULO_TETTO = 5;

/**
 * La pagina è in inglese? Sulle pagine EN la lingua la imposta Polylang (en_GB); negli invii
 * la imposta lidia_modulo_lingua() a partire dal campo nascosto `lingua`.
 *
 * @return bool
 */
function lidia_modulo_en() {
	return 0 === strpos( get_locale(), 'en' );
}

/**
 * Negli invii (REST e senza JavaScript) la lingua non arriva da Polylang: la porta il
 * modulo. Se è inglese, messaggi e conferma si scrivono in inglese.
 *
 * @param array $grezzo Dati grezzi dell'invio.
 */
function lidia_modulo_lingua( $grezzo ) {
	if ( ! isset( $grezzo['lingua'] ) || 'en' !== $grezzo['lingua'] || lidia_modulo_en() ) {
		return;
	}

	// Non switch_to_locale(): vuole il pacchetto di lingua di WordPress installato, e qui
	// serve solo la traduzione del tema (languages/en_GB.mo).
	add_filter(
		'locale',
		static function () {
			return 'en_GB';
		}
	);
	unload_textdomain( 'lidia', true );
	load_textdomain( 'lidia', LIDIA_DIR . '/languages/en_GB.mo', 'en_GB' );
}

/**
 * Le professioni. Elenco chiuso: in Delera si segmenta.
 *
 * @return array<string, string>
 */
function lidia_modulo_professioni() {
	return array(
		'avvocato'         => __( 'Avvocato', 'lidia' ),
		'socio'            => __( 'Socio o titolare', 'lidia' ),
		'direzione-legale' => __( 'Direzione legale d’impresa', 'lidia' ),
		'assicurazioni'    => __( 'Assicurazioni', 'lidia' ),
		'ateneo'           => __( 'Ateneo', 'lidia' ),
		'altro'            => __( 'Altro', 'lidia' ),
	);
}

/** Le chiavi di campagna che ribaltiamo dentro il payload (`campagna`). */
function lidia_modulo_chiavi_campagna() {
	return array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'wbraid', 'gbraid', 'fbclid', 'li_fat_id', 'msclkid' );
}

/* -------------------------------------------------------------------------
 * Attribuzione: primo, intermedi, ultimo contatto
 *
 * Il browser (assets/js/modulo.js) riconosce i contatti. Con il consenso marketing la storia sta
 * nel cookie `lidia_attribuzione` (90 giorni); senza, arriva solo il contatto della visita, nel
 * campo nascosto `contatto`. Specifica: docs/11-form-delera.md §2 «Attribuzione».
 * ---------------------------------------------------------------------- */

/** Il cookie di attribuzione: first-party, 90 giorni, lo scrive assets/js/modulo.js. */
const LIDIA_COOKIE_ATTRIBUZIONE = 'lidia_attribuzione';

/** Il cookie di Complianz che dice se c'è il consenso marketing. */
const LIDIA_COOKIE_CONSENSO_MARKETING = 'cmplz_marketing';

/** Quanti contatti intermedi si tengono, al massimo. */
const LIDIA_MAX_INTERMEDI = 5;

/**
 * C'è il consenso marketing di Complianz?
 *
 * @return bool
 */
function lidia_modulo_consenso_marketing() {
	return isset( $_COOKIE[ LIDIA_COOKIE_CONSENSO_MARKETING ] ) && 'allow' === sanitize_text_field( wp_unslash( $_COOKIE[ LIDIA_COOKIE_CONSENSO_MARKETING ] ) );
}

/**
 * Un contatto ripulito. Arriva dal browser: non ci si fida di niente.
 *
 * @param mixed $grezzo Contatto decodificato.
 * @return array|null
 */
function lidia_modulo_pulisci_contatto( $grezzo ) {
	if ( ! is_array( $grezzo ) ) {
		return null;
	}

	$prendi = static function ( $chiave, $quanto = 100 ) use ( $grezzo ) {
		return isset( $grezzo[ $chiave ] ) && is_scalar( $grezzo[ $chiave ] ) ? substr( sanitize_text_field( (string) $grezzo[ $chiave ] ), 0, $quanto ) : '';
	};

	$contatto = array(
		'utm_source'   => $prendi( 'utm_source' ),
		'utm_medium'   => $prendi( 'utm_medium' ),
		'utm_campaign' => $prendi( 'utm_campaign' ),
		'utm_content'  => $prendi( 'utm_content' ),
		'utm_term'     => $prendi( 'utm_term' ),
		'pagina'       => $prendi( 'pagina' ),
		'data'         => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $prendi( 'data', 10 ) ) ? $prendi( 'data', 10 ) : '',
		'click'        => array(),
	);

	if ( isset( $grezzo['click'] ) && is_array( $grezzo['click'] ) ) {
		foreach ( lidia_modulo_chiavi_campagna() as $chiave ) {
			if ( 0 !== strpos( $chiave, 'utm_' ) && ! empty( $grezzo['click'][ $chiave ] ) && is_scalar( $grezzo['click'][ $chiave ] ) ) {
				$contatto['click'][ $chiave ] = substr( sanitize_text_field( (string) $grezzo['click'][ $chiave ] ), 0, 200 );
			}
		}
	}

	return '' === $contatto['utm_source'] ? null : $contatto;
}

/**
 * L'attribuzione del lead.
 *
 * Con il consenso e il cookie: la storia completa. Altrimenti solo l'ultimo contatto, quello
 * della visita, dal campo nascosto del modulo.
 *
 * @param mixed $contatto_visita Il campo `contatto` del modulo (JSON).
 * @return array primo, intermedi, ultimo (array o null), totali (int, 0 = sconosciuto).
 */
function lidia_modulo_attribuzione( $contatto_visita ) {
	$vuota = array(
		'primo'     => null,
		'intermedi' => array(),
		'ultimo'    => null,
		'totali'    => 0,
	);

	if ( lidia_modulo_consenso_marketing() && ! empty( $_COOKIE[ LIDIA_COOKIE_ATTRIBUZIONE ] ) && is_string( $_COOKIE[ LIDIA_COOKIE_ATTRIBUZIONE ] ) ) {
		$storia = json_decode( wp_unslash( $_COOKIE[ LIDIA_COOKIE_ATTRIBUZIONE ] ), true );

		if ( is_array( $storia ) && isset( $storia['primo'] ) ) {
			$intermedi = array();

			if ( isset( $storia['intermedi'] ) && is_array( $storia['intermedi'] ) ) {
				foreach ( array_slice( $storia['intermedi'], -LIDIA_MAX_INTERMEDI ) as $passo ) {
					$passo = lidia_modulo_pulisci_contatto( $passo );

					if ( $passo ) {
						$intermedi[] = $passo;
					}
				}
			}

			$primo  = lidia_modulo_pulisci_contatto( $storia['primo'] );
			$ultimo = isset( $storia['ultimo'] ) ? lidia_modulo_pulisci_contatto( $storia['ultimo'] ) : null;

			if ( $primo ) {
				return array(
					'primo'     => $primo,
					'intermedi' => $intermedi,
					'ultimo'    => $ultimo ? $ultimo : $primo,
					'totali'    => isset( $storia['totali'] ) ? max( 1, absint( $storia['totali'] ) ) : 1,
				);
			}
		}
	}

	if ( is_string( $contatto_visita ) && '' !== $contatto_visita ) {
		$vuota['ultimo'] = lidia_modulo_pulisci_contatto( json_decode( wp_unslash( $contatto_visita ), true ) );
	}

	return $vuota;
}

/**
 * `campagna` (il campo storico del payload): l'ultimo contatto, tutte le chiavi, vuote se mancano.
 *
 * @param array|null $ultimo Ultimo contatto.
 * @return array<string, string>
 */
function lidia_modulo_campagna( $ultimo ) {
	$campagna = array_fill_keys( lidia_modulo_chiavi_campagna(), '' );

	if ( ! $ultimo ) {
		return $campagna;
	}

	foreach ( array_keys( $campagna ) as $chiave ) {
		if ( isset( $ultimo[ $chiave ] ) ) {
			$campagna[ $chiave ] = $ultimo[ $chiave ];
		} elseif ( isset( $ultimo['click'][ $chiave ] ) ) {
			$campagna[ $chiave ] = $ultimo['click'][ $chiave ];
		}
	}

	return $campagna;
}

/**
 * Da quale modulo arriva il lead, con il nome che usa Delera.
 *
 * @param string $tipo    prova o whitepaper.
 * @param string $intento prova o commerciale.
 * @return string
 */
function lidia_modulo_form_origine( $tipo, $intento ) {
	if ( 'whitepaper' === $tipo ) {
		return 'Paper';
	}

	return 'commerciale' === $intento ? 'Richiesta contatto' : 'Prova gratuita';
}

/**
 * Le posizioni ammesse per il blocco: servono a distinguere in GA4 il modulo in cima da
 * quello in fondo alla stessa pagina.
 *
 * @return array<string, string>
 */
function lidia_modulo_posizioni() {
	return array(
		''       => __( 'Non indicata', 'lidia' ),
		'hero'   => __( 'In cima alla pagina', 'lidia' ),
		'footer' => __( 'In fondo alla pagina', 'lidia' ),
		'inline' => __( 'Nel corpo della pagina', 'lidia' ),
	);
}

/**
 * L'URL della pagina in cui il modulo sta, senza il gettone di stato.
 *
 * È il valore di partenza: se JavaScript gira, lo sostituisce con l'indirizzo vero del browser,
 * perché quello stampato qui può venire dalla cache.
 *
 * @return string
 */
function lidia_modulo_url_corrente() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : wp_parse_url( home_url(), PHP_URL_HOST );
	$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$url  = ( is_ssl() ? 'https://' : 'http://' ) . $host . $path;

	return esc_url_raw( remove_query_arg( 'lidia', $url ) );
}

/* -------------------------------------------------------------------------
 * Antispam — quattro difese di prima parte, zero servizi esterni.
 * ---------------------------------------------------------------------- */

/**
 * Marca temporale firmata, da mettere nel modulo.
 *
 * @return string
 */
function lidia_modulo_marca() {
	$ora = time();

	return $ora . '.' . hash_hmac( 'sha256', (string) $ora, wp_salt( 'lidia-modulo' ) );
}

/**
 * La marca è nostra e ha l'età giusta?
 *
 * @param string $marca Marca ricevuta.
 * @return bool
 */
function lidia_modulo_marca_valida( $marca ) {
	$pezzi = explode( '.', (string) $marca, 2 );

	if ( 2 !== count( $pezzi ) ) {
		return false;
	}

	list( $ora, $firma ) = $pezzi;

	if ( ! hash_equals( hash_hmac( 'sha256', $ora, wp_salt( 'lidia-modulo' ) ), $firma ) ) {
		return false;
	}

	$eta = time() - (int) $ora;

	return $eta >= LIDIA_MODULO_ATTESA && $eta <= LIDIA_MODULO_SCADENZA;
}

/**
 * L'indirizzo IP del visitatore.
 *
 * Dietro un proxy o una CDN (SiteGround CDN, Cloudflare) REMOTE_ADDR è l'indirizzo del proxy,
 * uguale per tutti: il limite di frequenza bloccherebbe l'intero sito al sesto invio dell'ora.
 * Con `LIDIA_DIETRO_PROXY` a true in wp-config.php si legge l'indirizzo che il proxy passa.
 * Non si attiva da solo: quelle intestazioni le può scrivere chiunque, si leggono solo quando
 * davanti c'è davvero un proxy che le sovrascrive.
 *
 * @return string
 */
function lidia_modulo_ip() {
	if ( defined( 'LIDIA_DIETRO_PROXY' ) && LIDIA_DIETRO_PROXY ) {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR' ) as $chiave ) {
			if ( empty( $_SERVER[ $chiave ] ) ) {
				continue;
			}

			$lista = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $chiave ] ) ) );
			$ip    = trim( $lista[0] );

			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}

	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'ignoto';
}

/**
 * Chiave di frequenza: hash dell'IP col salt del sito. L'IP in chiaro non si scrive mai.
 *
 * @return string
 */
function lidia_modulo_chiave_frequenza() {
	return 'lidia_mod_' . substr( hash_hmac( 'sha256', lidia_modulo_ip(), wp_salt( 'lidia-modulo' ) ), 0, 20 );
}

/**
 * Troppi invii da questa connessione?
 *
 * @return bool
 */
function lidia_modulo_troppi_invii() {
	return (int) get_transient( lidia_modulo_chiave_frequenza() ) >= LIDIA_MODULO_TETTO;
}

/** Segna un invio. Si conta ogni tentativo, riuscito o no: anche i tentativi a vuoto costano. */
function lidia_modulo_conta_invio() {
	$chiave = lidia_modulo_chiave_frequenza();

	set_transient( $chiave, (int) get_transient( $chiave ) + 1, HOUR_IN_SECONDS );
}

/**
 * La richiesta arriva dal nostro host?
 *
 * @return bool
 */
function lidia_modulo_origine_nostra() {
	$nostro = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );

	foreach ( array( 'HTTP_ORIGIN', 'HTTP_REFERER' ) as $chiave ) {
		if ( empty( $_SERVER[ $chiave ] ) ) {
			continue;
		}

		$host = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER[ $chiave ] ) ), PHP_URL_HOST );

		if ( $host ) {
			return strtolower( $host ) === $nostro;
		}
	}

	// Nessuna delle due intestazioni: non basta per rifiutare una persona vera.
	return true;
}

/**
 * Passaggio innestabile per una verifica esterna (Turnstile, se un giorno servirà).
 *
 * @return bool
 */
function lidia_modulo_verifica_esterna() {
	return (bool) apply_filters( 'lidia_modulo_verifica_esterna', true );
}

/* -------------------------------------------------------------------------
 * Il documento del whitepaper
 * ---------------------------------------------------------------------- */

/**
 * Il documento indicato è un whitepaper pubblicato?
 *
 * L'ID arriva da un campo nascosto, quindi dal browser: va verificato, non creduto.
 *
 * @param int $documento ID.
 * @return bool
 */
function lidia_modulo_documento_valido( $documento ) {
	$documento = (int) $documento;

	if ( ! $documento ) {
		return false;
	}

	$post = get_post( $documento );

	return $post && 'risorsa' === $post->post_type && 'publish' === $post->post_status;
}

/**
 * Il whitepaper richiede il modulo prima del download?
 *
 * @param int $documento ID.
 * @return bool
 */
function lidia_modulo_documento_gated( $documento ) {
	$valore = get_post_meta( (int) $documento, 'lidia_gated', true );

	// Il campo ha default true: se non è mai stato salvato, il documento è protetto.
	return '' === $valore || rest_sanitize_boolean( $valore );
}

/* -------------------------------------------------------------------------
 * Raccolta e validazione
 * ---------------------------------------------------------------------- */

/**
 * Normalizza quello che è arrivato. Non valida: pulisce.
 *
 * @param array $grezzo Dati grezzi, già senza slash.
 * @return array
 */
function lidia_modulo_raccogli( $grezzo ) {
	$prendi = static function ( $chiave ) use ( $grezzo ) {
		return isset( $grezzo[ $chiave ] ) && is_scalar( $grezzo[ $chiave ] ) ? sanitize_text_field( (string) $grezzo[ $chiave ] ) : '';
	};

	// L'attribuzione: dal cookie con il consenso marketing, altrimenti il contatto della visita.
	$attribuzione = lidia_modulo_attribuzione( isset( $grezzo['contatto'] ) && is_scalar( $grezzo['contatto'] ) ? substr( (string) $grezzo['contatto'], 0, 2000 ) : '' );

	$posizione = $prendi( 'posizione' );

	return array(
		'tipo'               => 'whitepaper' === $prendi( 'tipo' ) ? 'whitepaper' : 'prova',
		'intento'            => 'commerciale' === $prendi( 'intento' ) ? 'commerciale' : 'prova',
		'nome'               => substr( $prendi( 'nome' ), 0, 80 ),
		'cognome'            => substr( $prendi( 'cognome' ), 0, 80 ),
		'email'              => substr( sanitize_email( $prendi( 'email' ) ), 0, 150 ),
		'telefono'           => substr( $prendi( 'telefono' ), 0, 40 ),
		'azienda'            => substr( $prendi( 'azienda' ), 0, 120 ),
		'professione'        => $prendi( 'professione' ),
		'consenso_privacy'   => ! empty( $grezzo['consenso_privacy'] ),
		'consenso_marketing' => ! empty( $grezzo['consenso_marketing'] ),
		'documento'          => absint( isset( $grezzo['documento'] ) ? $grezzo['documento'] : 0 ),
		'origine'            => esc_url_raw( isset( $grezzo['origine'] ) && is_scalar( $grezzo['origine'] ) ? (string) $grezzo['origine'] : '' ),
		'istanza'            => substr( $prendi( 'istanza' ), 0, 40 ),
		'posizione'          => array_key_exists( $posizione, lidia_modulo_posizioni() ) ? $posizione : '',
		'lingua'             => 'en' === $prendi( 'lingua' ) ? 'en' : 'it',
		'attribuzione'       => $attribuzione,
		'campagna'           => lidia_modulo_campagna( $attribuzione['ultimo'] ),
	);
}

/**
 * Cosa manca. Chiave = campo, valore = messaggio.
 *
 * @param array $dati Dati normalizzati.
 * @return array<string, string>
 */
function lidia_modulo_valida( $dati ) {
	$errori = array();
	$manca  = __( 'Serve anche questo.', 'lidia' );

	foreach ( array( 'nome', 'cognome', 'email', 'azienda', 'professione' ) as $campo ) {
		if ( '' === $dati[ $campo ] ) {
			$errori[ $campo ] = $manca;
		}
	}

	// 05/10: il telefono è obbligatorio nel modulo prova (anche intento commerciale), non nel whitepaper.
	if ( 'prova' === $dati['tipo'] && '' === $dati['telefono'] ) {
		$errori['telefono'] = $manca;
	}

	if ( '' !== $dati['email'] && ! is_email( $dati['email'] ) ) {
		$errori['email'] = __( 'Questo indirizzo non sembra valido.', 'lidia' );
	}

	if ( '' !== $dati['professione'] && ! array_key_exists( $dati['professione'], lidia_modulo_professioni() ) ) {
		$errori['professione'] = $manca;
	}

	if ( ! $dati['consenso_privacy'] ) {
		$errori['consenso_privacy'] = __( 'Senza questo consenso non possiamo ricontattarvi.', 'lidia' );
	}

	return $errori;
}

/* -------------------------------------------------------------------------
 * Markup
 * ---------------------------------------------------------------------- */

/**
 * Un campo.
 *
 * @param array $c nome, etichetta, tipo, aiuto, obbligatorio, valore, errore, autocomplete, voci, istanza.
 * @return string
 */
function lidia_modulo_campo( $c ) {
	$id     = 'lm-' . $c['istanza'] . '-' . $c['nome'];
	$aiuto  = ! empty( $c['aiuto'] ) ? $id . '-aiuto' : '';
	$errore = ! empty( $c['errore'] ) ? $id . '-errore' : '';
	$descr  = trim( $aiuto . ' ' . $errore );

	$html  = '<p class="lidia-campo' . ( $errore ? ' lidia-campo--errore' : '' ) . '">';
	$html .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $c['etichetta'] );

	if ( empty( $c['obbligatorio'] ) ) {
		$html .= ' <span class="lidia-campo-nota">' . esc_html__( '(facoltativo)', 'lidia' ) . '</span>';
	}

	$html .= '</label>';

	if ( 'select' === $c['tipo'] ) {
		$html .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $c['nome'] ) . '"';
		$html .= $descr ? ' aria-describedby="' . esc_attr( $descr ) . '"' : '';
		$html .= empty( $c['obbligatorio'] ) ? '' : ' required';
		$html .= '><option value="">' . esc_html__( 'Scegliete una voce', 'lidia' ) . '</option>';

		foreach ( $c['voci'] as $chiave => $etichetta ) {
			$html .= '<option value="' . esc_attr( $chiave ) . '"' . selected( $c['valore'], $chiave, false ) . '>' . esc_html( $etichetta ) . '</option>';
		}

		$html .= '</select>';
	} else {
		$html .= '<input type="' . esc_attr( $c['tipo'] ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $c['nome'] ) . '" value="' . esc_attr( $c['valore'] ) . '"';
		$html .= $descr ? ' aria-describedby="' . esc_attr( $descr ) . '"' : '';
		$html .= empty( $c['autocomplete'] ) ? '' : ' autocomplete="' . esc_attr( $c['autocomplete'] ) . '"';
		$html .= empty( $c['obbligatorio'] ) ? '' : ' required';
		$html .= '>';
	}

	if ( $aiuto ) {
		$html .= '<span class="lidia-campo-aiuto" id="' . esc_attr( $aiuto ) . '">' . esc_html( $c['aiuto'] ) . '</span>';
	}

	if ( $errore ) {
		$html .= '<strong class="lidia-campo-errore" id="' . esc_attr( $errore ) . '">' . esc_html( $c['errore'] ) . '</strong>';
	}

	return $html . '</p>';
}

/**
 * I due consensi.
 *
 * @param string $tipo    prova o whitepaper: cambia la finalità dichiarata.
 * @param string $istanza Istanza.
 * @param array  $dati    Valori.
 * @param array  $errori  Errori.
 * @return string
 */
function lidia_modulo_consensi( $tipo, $istanza, $dati, $errori ) {
	$privacy = 'lm-' . $istanza . '-privacy';
	$mktg    = 'lm-' . $istanza . '-marketing';
	$errore  = isset( $errori['consenso_privacy'] ) ? $errori['consenso_privacy'] : '';

	$finalita = 'whitepaper' === $tipo
		? __( ' e acconsento al trattamento dei dati per ricevere il documento ed essere ricontattato.', 'lidia' )
		: __( ' e acconsento al trattamento dei dati per essere ricontattato.', 'lidia' );

	$html  = '<div class="lidia-consensi">';
	$html .= '<p class="lidia-consenso' . ( $errore ? ' lidia-campo--errore' : '' ) . '">';
	$html .= '<input type="checkbox" id="' . esc_attr( $privacy ) . '" name="consenso_privacy" value="1"' . checked( $dati['consenso_privacy'], true, false );
	$html .= $errore ? ' aria-describedby="' . esc_attr( $privacy ) . '-errore"' : '';
	$html .= ' required>';
	$html .= '<label for="' . esc_attr( $privacy ) . '">';
	$html .= esc_html__( 'Ho letto l’', 'lidia' );
	$html .= '<a href="' . esc_url( home_url( '/legale/privacy/' ) ) . '">' . esc_html__( 'informativa privacy', 'lidia' ) . '</a>';
	$html .= esc_html( $finalita );
	$html .= '</label>';

	if ( $errore ) {
		$html .= '<strong class="lidia-campo-errore" id="' . esc_attr( $privacy ) . '-errore">' . esc_html( $errore ) . '</strong>';
	}

	$html .= '</p>';
	$html .= '<p class="lidia-consenso">';
	$html .= '<input type="checkbox" id="' . esc_attr( $mktg ) . '" name="consenso_marketing" value="1"' . checked( $dati['consenso_marketing'], true, false ) . '>';
	$html .= '<label for="' . esc_attr( $mktg ) . '">' . esc_html__( 'Voglio ricevere aggiornamenti su normativa e AI legale. Ci si cancella con un clic.', 'lidia' ) . '</label>';
	$html .= '</p></div>';

	return $html;
}

/**
 * Il modulo.
 *
 * @param string $tipo      prova o whitepaper.
 * @param string $istanza   Istanza.
 * @param string $posizione hero, footer, inline o vuoto.
 * @param array  $dati      Valori da ripristinare.
 * @param array  $errori    Errori da mostrare.
 * @param string $intento   prova o commerciale (solo per il tipo prova).
 * @return string
 */
function lidia_modulo_form( $tipo, $istanza, $posizione = '', $dati = array(), $errori = array(), $intento = 'prova' ) {
	$intento = ( 'prova' === $tipo && 'commerciale' === $intento ) ? 'commerciale' : 'prova';

	$dati = wp_parse_args(
		$dati,
		array(
			'intento'            => $intento,
			'nome'               => '',
			'cognome'            => '',
			'email'              => '',
			'telefono'           => '',
			'azienda'            => '',
			'professione'        => '',
			'consenso_privacy'   => false,
			'consenso_marketing' => false,
		)
	);

	$documento = 'whitepaper' === $tipo ? (int) get_queried_object_id() : 0;

	$html = '<form class="lidia-modulo" id="lm-' . esc_attr( $istanza ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-tipo="' . esc_attr( $tipo ) . '" data-posizione="' . esc_attr( $posizione ) . '" novalidate>';

	if ( ! empty( $errori['generale'] ) ) {
		$html .= '<p class="lidia-modulo-allarme" role="alert">' . esc_html( $errori['generale'] ) . '</p>';
	}

	$html .= '<input type="hidden" name="action" value="lidia_modulo">';
	$html .= '<input type="hidden" name="tipo" value="' . esc_attr( $tipo ) . '">';
	$html .= '<input type="hidden" name="lingua" value="' . ( lidia_modulo_en() ? 'en' : 'it' ) . '">';
	$html .= '<input type="hidden" name="istanza" value="' . esc_attr( $istanza ) . '">';
	$html .= '<input type="hidden" name="posizione" value="' . esc_attr( $posizione ) . '">';
	$html .= '<input type="hidden" name="intento" value="' . esc_attr( $intento ) . '">';
	$html .= '<input type="hidden" name="documento" value="' . esc_attr( (string) $documento ) . '">';
	$html .= '<input type="hidden" name="origine" value="' . esc_url( lidia_modulo_url_corrente() ) . '" data-lidia="origine">';
	$html .= '<input type="hidden" name="marca" value="' . esc_attr( lidia_modulo_marca() ) . '" data-lidia="marca">';

	// Il contatto della visita: lo scrive il browser all'invio. Il server non legge mai l'URL della
	// pagina (finirebbe in cache); la storia completa la legge dal cookie, con il consenso.
	$html .= '<input type="hidden" name="contatto" value="" data-lidia="contatto">';

	// L'esca: fuori schermo, non display:none, che i bot leggono nel CSS.
	$html .= '<div class="lidia-esca" aria-hidden="true"><label for="lm-' . esc_attr( $istanza ) . '-sito">' . esc_html__( 'Sito web', 'lidia' ) . '</label>';
	$html .= '<input type="text" id="lm-' . esc_attr( $istanza ) . '-sito" name="sito_web" tabindex="-1" autocomplete="off"></div>';

	/*
	 * 23/09: la scelta iniziale prova / commerciale è stata tolta dal modulo.
	 * 25/09: l'intento torna come attributo del blocco, fissato da chi compone la pagina:
	 * `prova` (home, /prova-gratuita/) o `commerciale` (/contatti/, per l'offerta Enterprise:
	 * personalizzazione e incontro). Stesso webhook: in Delera si distingue da `intento`.
	 */

	$campi = array(
		array(
			'nome'         => 'nome',
			'etichetta'    => __( 'Nome', 'lidia' ),
			'tipo'         => 'text',
			'obbligatorio' => true,
			'autocomplete' => 'given-name',
		),
		array(
			'nome'         => 'cognome',
			'etichetta'    => __( 'Cognome', 'lidia' ),
			'tipo'         => 'text',
			'obbligatorio' => true,
			'autocomplete' => 'family-name',
		),
		array(
			'nome'         => 'email',
			'etichetta'    => __( 'Email di lavoro', 'lidia' ),
			'tipo'         => 'email',
			'obbligatorio' => true,
			'autocomplete' => 'email',
			'aiuto'        => ( 'prova' === $tipo && 'prova' === $intento ) ? __( 'Usiamo questo indirizzo per gli accessi alla prova.', 'lidia' ) : '',
		),
	);

	if ( 'prova' === $tipo ) {
		$campi[] = array(
			'nome'         => 'telefono',
			'etichetta'    => __( 'Telefono', 'lidia' ),
			'tipo'         => 'tel',
			'obbligatorio' => true,
			'autocomplete' => 'tel',
			'aiuto'        => 'prova' === $intento ? __( "Per fissare la presentazione.", 'lidia' ) : '',
		);
	}

	$campi[] = array(
		'nome'         => 'azienda',
		'etichetta'    => __( 'Studio o azienda', 'lidia' ),
		'tipo'         => 'text',
		'obbligatorio' => true,
		'autocomplete' => 'organization',
	);

	$campi[] = array(
		'nome'         => 'professione',
		'etichetta'    => __( 'Professione', 'lidia' ),
		'tipo'         => 'select',
		'obbligatorio' => true,
		'voci'         => lidia_modulo_professioni(),
	);

	$html .= '<div class="lidia-modulo-griglia">';

	foreach ( $campi as $campo ) {
		$campo['istanza'] = $istanza;
		$campo['valore']  = $dati[ $campo['nome'] ];
		$campo['errore']  = isset( $errori[ $campo['nome'] ] ) ? $errori[ $campo['nome'] ] : '';
		$html            .= lidia_modulo_campo( $campo );
	}

	$html .= '</div>';
	$html .= lidia_modulo_consensi( $tipo, $istanza, $dati, $errori );

	if ( 'whitepaper' === $tipo ) {
		$prova       = __( 'Scarica il whitepaper', 'lidia' );
		$commerciale = '';
	} else {
		$prova       = __( 'Richiedi la prova gratuita', 'lidia' );
		$commerciale = __( 'Scrivi al team commerciale', 'lidia' );
	}

	$adesso = ( 'commerciale' === $intento && $commerciale ) ? $commerciale : $prova;

	// La classe `wp-element-button` eredita il pulsante del sito da theme.json: niente stile doppio.
	$html .= '<p class="lidia-modulo-invio"><button type="submit" class="wp-element-button lidia-modulo-invia"';
	$html .= ' data-prova="' . esc_attr( $prova ) . '"';
	$html .= $commerciale ? ' data-commerciale="' . esc_attr( $commerciale ) . '"' : '';
	$html .= ' data-invio="' . esc_attr__( 'Invio in corso…', 'lidia' ) . '">';
	$html .= esc_html( $adesso );
	$html .= '</button></p>';

	return $html . '</form>';
}

/**
 * Il documento non è protetto: al posto del modulo, il pulsante di download.
 *
 * @param int $documento ID del whitepaper.
 * @return string
 */
function lidia_modulo_download_libero( $documento ) {
	$link = lidia_delera_link_download( $documento );

	$slug = (string) get_post_field( 'post_name', $documento );

	return '<p class="lidia-modulo-invio"><a class="wp-element-button lidia-modulo-invia" href="' . esc_url( $link ) . '" data-track="download" data-track-documento="' . esc_attr( $slug ) . '" download>' . esc_html__( 'Scarica il whitepaper', 'lidia' ) . '</a></p>';
}

/**
 * La conferma che prende il posto del modulo.
 *
 * @param array $esito tipo, intento, download.
 * @return string
 */
function lidia_modulo_conferma( $esito ) {
	$tipo = isset( $esito['tipo'] ) ? $esito['tipo'] : 'prova';
	$html = '<div class="lidia-modulo-conferma" tabindex="-1" role="status">';

	if ( 'whitepaper' === $tipo ) {
		$link  = isset( $esito['download'] ) ? $esito['download'] : '';
		$html .= '<h3>' . esc_html__( 'Il download è partito', 'lidia' ) . '</h3><p>';
		$html .= esc_html__( 'Se non è partito, ', 'lidia' );
		$documento = isset( $esito['documento'] ) ? $esito['documento'] : '';
		$html     .= '<a href="' . esc_url( $link ) . '" data-track="download" data-track-documento="' . esc_attr( $documento ) . '" download>' . esc_html__( 'eccolo qui', 'lidia' ) . '</a>.';

		// La copia per posta la manda Delera con una sua automazione. La riga compare solo se
		// quell'automazione esiste davvero: lo dichiara wp-config.php, non il tema.
		if ( defined( 'LIDIA_WHITEPAPER_MAIL_DELERA' ) && LIDIA_WHITEPAPER_MAIL_DELERA ) {
			$html .= ' ' . esc_html__( 'Ne abbiamo mandato copia anche al vostro indirizzo.', 'lidia' );
		}

		return $html . '</p></div>';
	}

	$html .= '<h3>' . esc_html__( 'Richiesta ricevuta', 'lidia' ) . '</h3>';

	if ( isset( $esito['intento'] ) && 'commerciale' === $esito['intento'] ) {
		$html .= '<p>' . esc_html__( 'Il team commerciale vi risponde al più presto all’indirizzo che ci avete lasciato. Se volete anticipare qualcosa, scrivete a ', 'lidia' );
		$html .= '<a href="mailto:lidia@lidiatech.ai">lidia@lidiatech.ai</a>.</p>';

		return $html . '</div>';
	}

	$html .= '<p>' . esc_html__( 'Vi contattiamo a breve per fissare una presentazione della piattaforma. In quell’occasione attiviamo la prova gratuita.', 'lidia' ) . '</p>';
	$html .= '<p><a href="' . esc_url( home_url( lidia_modulo_en() ? '/en/security/' : '/sicurezza/' ) ) . '">' . esc_html__( 'Sicurezza e trattamento dei dati →', 'lidia' ) . '</a></p>';

	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Stato fra due richieste (senza JavaScript, e senza cookie)
 * ---------------------------------------------------------------------- */

/**
 * Mette da parte lo stato e restituisce il gettone.
 *
 * @param array $stato Stato.
 * @return string
 */
function lidia_modulo_salva_stato( $stato ) {
	$gettone = strtolower( wp_generate_password( 20, false, false ) );

	set_transient( 'lidia_stato_' . $gettone, $stato, 5 * MINUTE_IN_SECONDS );

	return $gettone;
}

/**
 * Riprende lo stato dal gettone in query string.
 *
 * @return array|null
 */
function lidia_modulo_stato() {
	static $stato = null;

	if ( null !== $stato ) {
		return $stato ? $stato : null;
	}

	$stato = false;

	if ( empty( $_GET['lidia'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return null;
	}

	$salvato = get_transient( 'lidia_stato_' . sanitize_key( wp_unslash( $_GET['lidia'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( is_array( $salvato ) ) {
		$stato = $salvato;
	}

	return $stato ? $stato : null;
}

/**
 * La pagina con il gettone di stato non va in cache: contiene i dati di una persona.
 */
function lidia_modulo_stato_non_in_cache() {
	if ( empty( $_GET['lidia'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	nocache_headers();
}
add_action( 'template_redirect', 'lidia_modulo_stato_non_in_cache', 0 );

/* -------------------------------------------------------------------------
 * Ricezione
 * ---------------------------------------------------------------------- */

/**
 * Elabora un invio. Restituisce l'esito, senza stampare niente.
 *
 * @param array $grezzo Dati grezzi, già senza slash.
 * @return array
 */
function lidia_modulo_elabora( $grezzo ) {
	$dati     = lidia_modulo_raccogli( $grezzo );
	$esca     = ! empty( $grezzo['sito_web'] );
	$marca    = lidia_modulo_marca_valida( isset( $grezzo['marca'] ) ? $grezzo['marca'] : '' );
	$generico = __( 'Non siamo riusciti a inviare la richiesta. I dati sono ancora qui: riprovate, oppure scriveteci a lidia@lidiatech.ai.', 'lidia' );

	// Esca e marca non producono un messaggio specifico: chi le sbaglia non è una persona.
	if ( $esca || ! $marca || ! lidia_modulo_origine_nostra() || ! lidia_modulo_verifica_esterna() ) {
		return array(
			'ok'     => false,
			'dati'   => $dati,
			'errori' => array( 'generale' => $generico ),
		);
	}

	if ( lidia_modulo_troppi_invii() ) {
		return array(
			'ok'     => false,
			'dati'   => $dati,
			'errori' => array( 'generale' => __( 'Troppe richieste da questa connessione. Riprovate fra qualche minuto.', 'lidia' ) ),
		);
	}

	// Da qui in giù è un tentativo di una persona: si conta, vada come vada.
	lidia_modulo_conta_invio();

	$errori = lidia_modulo_valida( $dati );

	if ( $errori ) {
		return array(
			'ok'     => false,
			'dati'   => $dati,
			'errori' => $errori,
		);
	}

	// Il whitepaper deve esistere davvero: l'ID arriva dal browser.
	if ( 'whitepaper' === $dati['tipo'] && ! lidia_modulo_documento_valido( $dati['documento'] ) ) {
		return array(
			'ok'     => false,
			'dati'   => $dati,
			'errori' => array( 'generale' => __( 'Il documento richiesto non è disponibile. Ricaricate la pagina e riprovate, oppure scriveteci a lidia@lidiatech.ai.', 'lidia' ) ),
		);
	}

	lidia_delera_consegna( $dati );

	$esito = array(
		'tipo'      => $dati['tipo'],
		'intento'   => $dati['intento'],
		'istanza'   => $dati['istanza'],
		'posizione' => $dati['posizione'],
		'documento' => '',
	);

	if ( 'whitepaper' === $dati['tipo'] ) {
		$esito['documento'] = (string) get_post_field( 'post_name', $dati['documento'] );
		$esito['download']  = lidia_delera_link_download( $dati['documento'] );
	}

	return array(
		'ok'    => true,
		'dati'  => $dati,
		'esito' => $esito,
	);
}

/**
 * Invio senza JavaScript: elabora e rimanda alla pagina di partenza.
 */
function lidia_modulo_ricevi() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing — nessun nonce, per scelta: docs/11-form-delera.md §3.
	$grezzo = wp_unslash( $_POST );
	// phpcs:enable

	lidia_modulo_lingua( $grezzo );

	$risultato = lidia_modulo_elabora( $grezzo );
	$ritorno   = $risultato['dati']['origine'] ? $risultato['dati']['origine'] : home_url( '/' );
	$ritorno   = wp_validate_redirect( $ritorno, home_url( '/' ) );

	$stato = $risultato['ok']
		? array(
			'ok'      => true,
			'esito'   => $risultato['esito'],
			'istanza' => $risultato['dati']['istanza'],
		)
		: array(
			'ok'      => false,
			'dati'    => $risultato['dati'],
			'errori'  => $risultato['errori'],
			'istanza' => $risultato['dati']['istanza'],
		);

	$destinazione = add_query_arg( 'lidia', lidia_modulo_salva_stato( $stato ), $ritorno );
	$ancora       = $risultato['dati']['istanza'] ? '#lm-' . rawurlencode( $risultato['dati']['istanza'] ) : '';

	wp_safe_redirect( $destinazione . $ancora, 303 );
	exit;
}
add_action( 'admin_post_nopriv_lidia_modulo', 'lidia_modulo_ricevi' );
add_action( 'admin_post_lidia_modulo', 'lidia_modulo_ricevi' );

/**
 * Invio con JavaScript.
 *
 * @param WP_REST_Request $richiesta Richiesta.
 * @return WP_REST_Response
 */
function lidia_modulo_rest( $richiesta ) {
	lidia_modulo_lingua( $richiesta->get_params() );

	$risultato = lidia_modulo_elabora( $richiesta->get_params() );

	if ( $risultato['ok'] ) {
		return new WP_REST_Response(
			array(
				'ok'    => true,
				'html'  => lidia_modulo_conferma( $risultato['esito'] ),
				'esito' => $risultato['esito'],
			),
			200
		);
	}

	return new WP_REST_Response(
		array(
			'ok'     => false,
			'errori' => $risultato['errori'],
		),
		200
	);
}

/**
 * Una marca fresca, per il browser. Risposta mai in cache.
 *
 * @return WP_REST_Response
 */
function lidia_modulo_rest_marca() {
	$risposta = new WP_REST_Response( array( 'marca' => lidia_modulo_marca() ), 200 );
	$risposta->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );

	return $risposta;
}

/** Registra le rotte. */
function lidia_modulo_rotta() {
	register_rest_route(
		'lidia/v1',
		'/modulo',
		array(
			'methods'             => 'POST',
			'callback'            => 'lidia_modulo_rest',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'lidia/v1',
		'/marca',
		array(
			'methods'             => 'GET',
			'callback'            => 'lidia_modulo_rest_marca',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'lidia_modulo_rotta' );

/* -------------------------------------------------------------------------
 * Il blocco
 * ---------------------------------------------------------------------- */

/**
 * Rende il blocco: il modulo, o la conferma se l'invio è appena andato a buon fine.
 *
 * @param array $attributi Attributi.
 * @return string
 */
function lidia_modulo_render( $attributi ) {
	static $contatore = 0;

	++$contatore;

	$tipo      = ( isset( $attributi['tipo'] ) && 'whitepaper' === $attributi['tipo'] ) ? 'whitepaper' : 'prova';
	$posizione = isset( $attributi['posizione'] ) && array_key_exists( $attributi['posizione'], lidia_modulo_posizioni() ) ? $attributi['posizione'] : '';
	$intento   = ( 'prova' === $tipo && isset( $attributi['intento'] ) && 'commerciale' === $attributi['intento'] ) ? 'commerciale' : 'prova';
	$istanza   = $tipo . '-' . $contatore;
	$stato     = lidia_modulo_stato();
	$suo       = ( $stato && ! empty( $stato['istanza'] ) ) ? $stato['istanza'] : '';

	wp_enqueue_script( 'lidia-modulo' );

	// Whitepaper non protetto: niente modulo, solo il pulsante.
	if ( 'whitepaper' === $tipo ) {
		$documento = (int) get_queried_object_id();

		if ( lidia_modulo_documento_valido( $documento ) && ! lidia_modulo_documento_gated( $documento ) ) {
			return '<div ' . get_block_wrapper_attributes( array( 'class' => 'lidia-modulo-blocco' ) ) . '>' . lidia_modulo_download_libero( $documento ) . '</div>';
		}
	}

	if ( $stato && $istanza === $suo ) {
		if ( ! empty( $stato['ok'] ) ) {
			$corpo = lidia_modulo_conferma( $stato['esito'] );

			// Senza JavaScript l'evento di conversione non partirebbe: lo aggiunge il server.
			wp_add_inline_script(
				'lidia-modulo',
				'window.dataLayer=window.dataLayer||[];window.dataLayer.push(' . wp_json_encode(
					array(
						'event'     => 'lidia_lead',
						'tipo'      => $stato['esito']['tipo'],
						'intento'   => isset( $stato['esito']['intento'] ) ? $stato['esito']['intento'] : '',
						'documento' => isset( $stato['esito']['documento'] ) ? $stato['esito']['documento'] : '',
						'posizione' => isset( $stato['esito']['posizione'] ) ? $stato['esito']['posizione'] : '',
					)
				) . ');',
				'before'
			);
		} else {
			$corpo = lidia_modulo_form( $tipo, $istanza, $posizione, $stato['dati'], $stato['errori'], $intento );
		}
	} else {
		$corpo = lidia_modulo_form( $tipo, $istanza, $posizione, array(), array(), $intento );
	}

	return '<div ' . get_block_wrapper_attributes( array( 'class' => 'lidia-modulo-blocco' ) ) . '>' . $corpo . '</div>';
}

/** Registra il blocco e i suoi script. */
function lidia_registra_modulo() {
	wp_register_script(
		'lidia-modulo',
		LIDIA_URI . '/assets/js/modulo.js',
		array(),
		lidia_versione_file( 'assets/js/modulo.js' ),
		true
	);

	wp_localize_script(
		'lidia-modulo',
		'lidiaModulo',
		array(
			'rotta'    => esc_url_raw( rest_url( 'lidia/v1/modulo' ) ),
			'marca'    => esc_url_raw( rest_url( 'lidia/v1/marca' ) ),
			'campagna' => lidia_modulo_chiavi_campagna(),
		)
	);

	wp_register_script(
		'lidia-modulo-editor',
		LIDIA_URI . '/assets/js/modulo-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		lidia_versione_file( 'assets/js/modulo-editor.js' ),
		true
	);

	register_block_type(
		'lidia/modulo',
		array(
			'api_version'     => 3,
			'title'           => __( 'Modulo Lidia', 'lidia' ),
			'category'        => 'widgets',
			'icon'            => 'feedback',
			'description'     => __( 'Il modulo di contatto del tema. Scrive su Delera via webhook: nessun iframe, nessun cookie, si vede sempre.', 'lidia' ),
			'editor_script'   => 'lidia-modulo-editor',
			'render_callback' => 'lidia_modulo_render',
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'tipo'      => array(
					'type'    => 'string',
					'default' => 'prova',
				),
				'posizione' => array(
					'type'    => 'string',
					'default' => '',
				),
				'intento'   => array(
					'type'    => 'string',
					'default' => 'prova',
				),
			),
		)
	);
}
add_action( 'init', 'lidia_registra_modulo' );
