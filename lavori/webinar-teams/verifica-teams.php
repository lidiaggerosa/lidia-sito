<?php
/**
 * 31 — Verifica del collegamento a Microsoft Teams (24/11/2026).
 *
 * Uso sul server:  wp eval-file ~/scripts/31-verifica-teams.php
 * Stampa quali costanti ci sono e la risposta di Microsoft alla richiesta del token.
 * Non stampa mai secret, token o ID completi.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

$costanti = array(
	'LIDIA_TEAMS_TENANT_ID',
	'LIDIA_TEAMS_CLIENT_ID',
	'LIDIA_TEAMS_CLIENT_SECRET',
	'LIDIA_TEAMS_SECRET_SCADENZA',
	'LIDIA_TEAMS_ORGANIZZATORE',
	'LIDIA_WEBINAR_TOKEN',
);

echo "Costanti in wp-config.php\n";

foreach ( $costanti as $nome ) {
	$c = defined( $nome ) && '' !== (string) constant( $nome );
	echo '  ' . str_pad( $nome, 30 ) . ( $c ? 'presente' : 'MANCA' ) . "\n";
}

if ( ! defined( 'LIDIA_TEAMS_TENANT_ID' ) || ! defined( 'LIDIA_TEAMS_CLIENT_ID' ) || ! defined( 'LIDIA_TEAMS_CLIENT_SECRET' ) ) {
	echo "\nEsito: mancano credenziali, la richiesta del token non parte.\n";
	return;
}

echo "\nRichiesta del token a Microsoft\n";

$risposta = wp_remote_post(
	'https://login.microsoftonline.com/' . rawurlencode( LIDIA_TEAMS_TENANT_ID ) . '/oauth2/v2.0/token',
	array(
		'timeout' => 15,
		'body'    => array(
			'grant_type'    => 'client_credentials',
			'client_id'     => LIDIA_TEAMS_CLIENT_ID,
			'client_secret' => LIDIA_TEAMS_CLIENT_SECRET,
			'scope'         => 'https://graph.microsoft.com/.default',
		),
	)
);

if ( is_wp_error( $risposta ) ) {
	echo '  Errore di rete: ' . $risposta->get_error_message() . "\n";
	return;
}

$codice = (int) wp_remote_retrieve_response_code( $risposta );
$corpo  = json_decode( (string) wp_remote_retrieve_body( $risposta ), true );

echo '  HTTP ' . $codice . "\n";

if ( 200 === $codice && ! empty( $corpo['access_token'] ) ) {
	// I permessi dentro il token: dicono se il consenso amministratore è arrivato.
	$parti = explode( '.', (string) $corpo['access_token'] );
	$dati  = isset( $parti[1] ) ? json_decode( base64_decode( strtr( $parti[1], '-_', '+/' ) ), true ) : array();
	$ruoli = isset( $dati['roles'] ) ? implode( ', ', (array) $dati['roles'] ) : '(nessuno)';

	echo "  Token ottenuto.\n";
	echo '  Permessi nel token: ' . $ruoli . "\n";

	// Tutti i webinar del tenant da oggi in poi, con chi li ha creati (25/11/2026).
	// Serve quando un webinar non compare in `wp lidia webinar elenco` (che mostra solo
	// quelli in cui LIDIA_TEAMS_ORGANIZZATORE è organizzatore).
	echo "\nWebinar del tenant da oggi in poi\n";

	$url   = 'https://graph.microsoft.com/v1.0/solutions/virtualEvents/webinars';
	$oggi  = gmdate( 'Y-m-d' );
	$visti = 0;

	while ( $url ) {
		$r = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $corpo['access_token'] ),
			)
		);

		if ( is_wp_error( $r ) ) {
			echo '  Errore di rete: ' . $r->get_error_message() . "\n";
			break;
		}

		$c = (int) wp_remote_retrieve_response_code( $r );
		$b = json_decode( (string) wp_remote_retrieve_body( $r ), true );

		if ( 200 !== $c ) {
			echo '  HTTP ' . $c . ' ' . ( isset( $b['error']['code'] ) ? $b['error']['code'] : '' ) . "\n";
			break;
		}

		foreach ( (array) ( isset( $b['value'] ) ? $b['value'] : array() ) as $w ) {
			$inizio = isset( $w['startDateTime']['dateTime'] ) ? substr( $w['startDateTime']['dateTime'], 0, 16 ) : '';

			if ( substr( $inizio, 0, 10 ) < $oggi ) {
				continue;
			}

			$autore = isset( $w['createdBy']['user']['displayName'] ) ? $w['createdBy']['user']['displayName'] : '';
			$autore = '' === $autore && isset( $w['createdBy']['user']['id'] ) ? $w['createdBy']['user']['id'] : $autore;

			echo '  ' . $inizio . ' | ' . ( isset( $w['displayName'] ) ? $w['displayName'] : '' ) . ' | ' . ( isset( $w['status'] ) ? $w['status'] : '' ) . ' | creato da: ' . $autore . "\n";
			echo '    id: ' . ( isset( $w['id'] ) ? $w['id'] : '' ) . "\n";
			++$visti;
		}

		$url = isset( $b['@odata.nextLink'] ) ? (string) $b['@odata.nextLink'] : '';
	}

	if ( ! $visti ) {
		echo "  Nessun webinar da oggi in poi.\n";
	}

	return;
}

$errore = isset( $corpo['error'] ) ? (string) $corpo['error'] : '';
$descr  = isset( $corpo['error_description'] ) ? strtok( (string) $corpo['error_description'], "\r\n" ) : '';

echo '  Errore: ' . $errore . "\n";
echo '  Dettaglio: ' . $descr . "\n";
