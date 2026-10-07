<?php
/**
 * Date originali degli articoli, dal manifest. Rieseguibile.
 *
 *   wp eval-file scripts/22-date-articoli.php
 *
 * Gli articoli (post) migrati dal Joomla avevano la data dell'import (17/09/2026).
 * Qui prendono il mese di pubblicazione originale indicato dall'owner il 07/10/2026:
 * Legge 132/2025 → ottobre 2025, Agenti AI → gennaio 2026. Si conosce solo mese e
 * anno: la data va al primo del mese e `single.html` mostra solo mese e anno.
 *
 * Tocca solo la data di pubblicazione: né il contenuto né gli autori (gli articoli
 * sono firmati Lidia). I whitepaper li gestisce 21-risorse-date-autori.php.
 * La data di modifica resta quella reale.
 *
 * Sul server il manifest si cerca in ~/scripts/blocchi/risorse/, in locale accanto a
 * questo file.
 *
 * @package Lidia
 */

$candidati = array(
	__DIR__ . '/blocchi/risorse/manifest.json',
	getenv( 'HOME' ) . '/scripts/blocchi/risorse/manifest.json',
);

$manifest = null;

foreach ( $candidati as $file ) {
	if ( is_readable( $file ) ) {
		$manifest = json_decode( (string) file_get_contents( $file ), true );
		break;
	}
}

if ( ! is_array( $manifest ) ) {
	WP_CLI::error( 'manifest.json non trovato o non valido.' );
}

// wp_update_post ripassa il contenuto dai filtri: senza utente, kses toglierebbe attributi.
kses_remove_filters();

foreach ( $manifest as $voce ) {
	if ( 'post' !== $voce['tipo'] || empty( $voce['data'] ) ) {
		continue;
	}

	$trovati = get_posts(
		array(
			'name'        => $voce['slug'],
			'post_type'   => 'post',
			'post_status' => 'any',
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	if ( ! $trovati ) {
		WP_CLI::warning( "Nessun articolo con slug {$voce['slug']}" );
		continue;
	}

	$id   = (int) $trovati[0];
	$data = $voce['data'] . '-01 09:00:00';

	wp_update_post(
		array(
			'ID'            => $id,
			'post_date'     => $data,
			'post_date_gmt' => get_gmt_from_date( $data ),
			'edit_date'     => true,
		)
	);

	WP_CLI::log( sprintf( '  %-45s %s → %d', $voce['slug'], $voce['data'], $id ) );
}

WP_CLI::success( 'Date degli articoli aggiornate.' );
