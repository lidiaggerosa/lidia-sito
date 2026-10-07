<?php
/**
 * Date originali, autori e testo dei whitepaper, dal manifest. Rieseguibile.
 *
 *   wp eval-file scripts/21-risorse-date-autori.php
 *
 * Sul server il manifest si cerca in ~/scripts/blocchi/risorse/, in locale accanto a
 * questo file. Per ogni voce con `data` (AAAA-MM) e `autori`:
 * - data di pubblicazione al primo del mese: il manifest conosce solo mese e anno, e
 *   `single-risorsa.html` mostra solo quelli;
 * - `lidia_autori` con i nomi in chiaro;
 * - il contenuto della pagina da `blocchi/risorse/<slug>.html`, accanto al manifest
 *   (sintesi nuove del 07/10/2026).
 * La data di modifica resta quella reale. L'ordine degli elenchi non cambia: segue
 * `menu_order`, non la data (scripts/05-risorse.md). (07/10/2026)
 *
 * @package Lidia
 */

$candidati = array(
	__DIR__ . '/blocchi/risorse/manifest.json',
	getenv( 'HOME' ) . '/scripts/blocchi/risorse/manifest.json',
);

$manifest = null;
$cartella = '';

foreach ( $candidati as $file ) {
	if ( is_readable( $file ) ) {
		$manifest = json_decode( (string) file_get_contents( $file ), true );
		$cartella = dirname( $file ) . '/';
		break;
	}
}

if ( ! is_array( $manifest ) ) {
	WP_CLI::error( 'manifest.json non trovato o non valido.' );
}

// Il contenuto viene dai nostri file: nessun filtro che tolga attributi.
kses_remove_filters();

foreach ( $manifest as $voce ) {
	if ( empty( $voce['data'] ) || empty( $voce['autori'] ) ) {
		continue;
	}

	$trovati = get_posts(
		array(
			'name'        => $voce['slug'],
			'post_type'   => $voce['tipo'],
			'post_status' => 'any',
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	if ( ! $trovati ) {
		WP_CLI::warning( "Nessun contenuto con slug {$voce['slug']}" );
		continue;
	}

	$id   = (int) $trovati[0];
	$data = $voce['data'] . '-01 09:00:00';

	$campi = array(
		'ID'            => $id,
		'post_date'     => $data,
		'post_date_gmt' => get_gmt_from_date( $data ),
		'edit_date'     => true,
	);

	$blocchi = $cartella . $voce['slug'] . '.html';

	if ( is_readable( $blocchi ) ) {
		$campi['post_content'] = (string) file_get_contents( $blocchi );
	}

	wp_update_post( wp_slash( $campi ) );
	update_post_meta( $id, 'lidia_autori', $voce['autori'] );

	WP_CLI::log( sprintf( '  %-45s %s  %s', $voce['slug'], $voce['data'], $voce['autori'] ) );
}

WP_CLI::success( 'Date e autori aggiornati.' );
