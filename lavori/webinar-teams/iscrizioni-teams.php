<?php
/**
 * 32 — Iscrizioni al webinar Teams di una pagina: elenco e annullamento (26/11/2026).
 *
 * Serve per togliere le iscrizioni di prova, che da Teams non si annullano.
 *
 * Uso sul server:
 *   wp eval-file ~/scripts/32-iscrizioni-teams.php <slug>                    → elenco
 *   wp eval-file ~/scripts/32-iscrizioni-teams.php <slug> annulla <email>    → annulla quell'iscrizione
 *
 * Usa le funzioni di inc/webinar.php (token, chiamate Graph) e l'ID Teams salvato sulla pagina.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

$slug   = isset( $args[0] ) ? (string) $args[0] : '';
$azione = isset( $args[1] ) ? (string) $args[1] : 'elenco';
$email  = isset( $args[2] ) ? strtolower( trim( (string) $args[2] ) ) : '';

if ( ! function_exists( 'lidia_teams_chiama' ) || ! function_exists( 'lidia_webinar_da_slug' ) ) {
	WP_CLI::error( 'Mancano le funzioni del tema (inc/webinar.php).' );
}

$webinar = lidia_webinar_da_slug( $slug );

if ( ! $webinar ) {
	WP_CLI::error( 'Nessun webinar pubblicato con slug «' . $slug . '».' );
}

$teams_id = (string) get_post_meta( $webinar->ID, 'lidia_webinar_teams_id', true );

if ( '' === $teams_id ) {
	WP_CLI::error( 'La pagina non ha l’ID del webinar Teams (lidia_webinar_teams_id).' );
}

$base = '/solutions/virtualEvents/webinars/' . lidia_teams_id_percorso( $teams_id ) . '/registrations';

// Tutte le iscrizioni, pagina per pagina.
$iscritti = array();
$percorso = $base;

while ( $percorso ) {
	$r = lidia_teams_chiama( 'GET', $percorso );

	if ( 200 !== $r['codice'] ) {
		WP_CLI::error( 'Graph: HTTP ' . $r['codice'] . ' ' . $r['errore'] );
	}

	foreach ( (array) ( isset( $r['corpo']['value'] ) ? $r['corpo']['value'] : array() ) as $i ) {
		$iscritti[] = $i;
	}

	$percorso = isset( $r['corpo']['@odata.nextLink'] ) ? (string) $r['corpo']['@odata.nextLink'] : '';
}

if ( 'annulla' !== $azione ) {
	if ( ! $iscritti ) {
		WP_CLI::warning( 'Nessun iscritto.' );
		return;
	}

	$righe = array();

	foreach ( $iscritti as $i ) {
		$righe[] = array(
			'nome'   => trim( ( isset( $i['firstName'] ) ? $i['firstName'] : '' ) . ' ' . ( isset( $i['lastName'] ) ? $i['lastName'] : '' ) ),
			'email'  => isset( $i['email'] ) ? $i['email'] : '',
			'stato'  => isset( $i['status'] ) ? $i['status'] : '',
			'data'   => isset( $i['registrationDateTime'] ) ? substr( (string) $i['registrationDateTime'], 0, 16 ) : '',
		);
	}

	WP_CLI\Utils\format_items( 'table', $righe, array( 'nome', 'email', 'stato', 'data' ) );
	WP_CLI::log( count( $righe ) . ' iscrizioni.' );
	return;
}

if ( ! is_email( $email ) ) {
	WP_CLI::error( 'Indica l’email da annullare: … annulla nome@dominio.it' );
}

$trovate = 0;

foreach ( $iscritti as $i ) {
	if ( strtolower( isset( $i['email'] ) ? (string) $i['email'] : '' ) !== $email ) {
		continue;
	}

	if ( isset( $i['status'] ) && 'canceled' === $i['status'] ) {
		WP_CLI::log( 'Già annullata.' );
		++$trovate;
		continue;
	}

	$r = lidia_teams_chiama( 'POST', $base . '/' . rawurlencode( (string) $i['id'] ) . '/cancel' );

	if ( $r['codice'] >= 200 && $r['codice'] < 300 ) {
		WP_CLI::success( 'Iscrizione di ' . $email . ' annullata.' );
	} else {
		WP_CLI::warning( 'Annullamento non riuscito: HTTP ' . $r['codice'] . ' ' . $r['errore'] );
	}

	++$trovate;
}

if ( ! $trovate ) {
	WP_CLI::warning( 'Nessuna iscrizione con l’email ' . $email . '.' );
}
