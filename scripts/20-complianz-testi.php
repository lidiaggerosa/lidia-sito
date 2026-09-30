<?php
/**
 * scripts/20-complianz-testi.php — testi dei pulsanti del banner Complianz.
 *
 * Complianz tiene i testi del banner nella sua tabella, non in un file: questo script
 * li scrive, così il banner si ricostruisce da `scripts/` (CLAUDE.md §6.0).
 * 30/09/2026: «Nega» diventa «Rifiuta».
 *
 * Uso, sul server:
 *   wp eval-file ~/scripts/20-complianz-testi.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CMPLZ_COOKIEBANNER' ) ) {
	WP_CLI::error( 'Complianz non è attivo.' );
}

global $wpdb;
$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}cmplz_cookiebanners" );

foreach ( $ids as $id ) {
	$banner  = new CMPLZ_COOKIEBANNER( (int) $id );
	$rifiuta = $banner->dismiss;

	if ( is_array( $rifiuta ) ) {
		$rifiuta['text'] = 'Rifiuta';
	} else {
		$rifiuta = 'Rifiuta';
	}

	$banner->dismiss = $rifiuta;
	$banner->save();
	WP_CLI::log( "Banner {$id}: pulsante di rifiuto = Rifiuta" );
}

WP_CLI::success( 'Testi del banner aggiornati.' );
