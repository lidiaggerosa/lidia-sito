<?php
/**
 * Sposta i PDF protetti da uploads/riservati/ alla cartella fuori dalla radice web.
 *
 * Una tantum, per i file caricati prima del 23/09/2026. Si lancia dal server:
 *   wp eval-file ~/scripts/13-sposta-riservati.php
 *
 * Per ogni whitepaper con `lidia_file`: sposta il file e le sue miniature, riscrive
 * `_wp_attached_file` con il solo nome, e stampa il risultato. Non tocca niente
 * che non sia già in uploads/riservati/.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Solo da WP-CLI.\n" );
}

$destinazione = lidia_delera_cartella_riservata_percorso();

if ( ! lidia_delera_cartella_riservata_pronta() ) {
	WP_CLI::error( "Cartella non scrivibile: $destinazione" );
}

$uploads  = wp_upload_dir();
$vecchia  = wp_normalize_path( $uploads['basedir'] ) . '/' . LIDIA_CARTELLA_RISERVATA;
$risorse  = get_posts(
	array(
		'post_type'      => 'risorsa',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => 'lidia_file',
		'fields'         => 'ids',
	)
);

foreach ( $risorse as $id ) {
	$allegato = (int) get_post_meta( $id, 'lidia_file', true );
	$attuale  = $allegato ? wp_normalize_path( (string) get_attached_file( $allegato ) ) : '';

	if ( ! $attuale || ! file_exists( $attuale ) ) {
		WP_CLI::warning( "Risorsa $id: allegato $allegato senza file." );
		continue;
	}

	if ( 0 === strpos( $attuale, $destinazione . '/' ) ) {
		WP_CLI::log( "Risorsa $id: già al posto giusto (" . basename( $attuale ) . ')' );
		continue;
	}

	if ( 0 !== strpos( $attuale, $vecchia . '/' ) ) {
		WP_CLI::warning( "Risorsa $id: il file non è in uploads/riservati/ ($attuale), lo lascio dov'è." );
		continue;
	}

	$nome  = basename( $attuale );
	$nuovo = $destinazione . '/' . $nome;

	if ( file_exists( $nuovo ) ) {
		WP_CLI::warning( "Risorsa $id: $nome esiste già nella destinazione, lo lascio dov'è." );
		continue;
	}

	if ( ! rename( $attuale, $nuovo ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		WP_CLI::warning( "Risorsa $id: spostamento di $nome fallito." );
		continue;
	}

	// Miniature generate dal PDF (nome-pdf.jpg, nome-pdf-300x400.jpg…).
	$base = pathinfo( $nome, PATHINFO_FILENAME );
	foreach ( glob( $vecchia . '/' . $base . '-pdf*.jpg' ) ?: array() as $miniatura ) {
		rename( $miniatura, $destinazione . '/' . basename( $miniatura ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
	}

	update_post_meta( $allegato, '_wp_attached_file', $nome );

	WP_CLI::success( "Risorsa $id: $nome → $destinazione/" );
}

WP_CLI::log( 'Rimasto in uploads/riservati/: ' . implode( ', ', array_map( 'basename', glob( $vecchia . '/*' ) ?: array() ) ) );
