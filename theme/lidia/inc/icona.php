<?php
/**
 * Favicon e icone del sito, dai file del tema.
 *
 * Le icone stanno in assets/icone/ e non nell'opzione «Icona del sito» del pannello: un'icona
 * caricata dall'admin vive nel database e non si ricostruisce da `scripts/` (canale unico).
 * Questo filtro vince comunque su un'icona eventualmente impostata dal pannello.
 *
 * WordPress usa la stessa funzione per i <link> nell'head (32, 180, 192, 270) e per
 * rispondere a /favicon.ico, quindi basta un filtro.
 * Sorgente: brand/favicon-512-originale.png (01/10/2026).
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * L'URL dell'icona per la misura richiesta.
 *
 * @param string $url  URL calcolato da WordPress.
 * @param int    $size Lato in pixel.
 * @return string
 */
function lidia_icona_url( $url, $size ) {
	$size = (int) $size;

	if ( 180 === $size ) {
		$file = 'apple-touch-icon.png'; // angoli pieni: iOS li arrotonda da sé.
	} elseif ( $size <= 32 ) {
		$file = 'icona-32.png';
	} elseif ( $size <= 192 ) {
		$file = 'icona-192.png';
	} elseif ( $size <= 270 ) {
		$file = 'icona-270.png';
	} else {
		$file = 'icona-512.png';
	}

	return LIDIA_URI . '/assets/icone/' . $file . '?v=' . LIDIA_VERSION;
}
add_filter( 'get_site_icon_url', 'lidia_icona_url', 10, 2 );

/** Il .ico per i client che lo cercano ancora. */
function lidia_icona_ico() {
	echo '<link rel="icon" href="' . esc_url( LIDIA_URI . '/assets/icone/favicon.ico' ) . '" sizes="48x48">' . "\n";
}
add_action( 'wp_head', 'lidia_icona_ico', 98 );
