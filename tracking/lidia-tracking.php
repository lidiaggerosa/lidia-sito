<?php
/**
 * Plugin Name: Lidia — Tracking & Consent
 * Description: Consent Mode v2 (default e aggiornamento da Complianz), contesto di pagina nel dataLayer, container GTM.
 * Version: 1.1.0
 *
 * Sorgente: Nuovo sito Lidia\tracking\lidia-tracking.php
 * Destinazione: wp-content/mu-plugins/lidia-tracking.php
 *
 * Unico posto in cui il sito parla di tracciamento. Nessun tag nel tema, nessuno
 * snippet nell'admin, nessun plugin "header & footer scripts".
 *
 * Gli ID stanno in wp-config.php, mai qui e mai nei file di progetto:
 *   define( 'LIDIA_ENV', 'production' );   // 'local' | 'staging' | 'production'
 *   define( 'LIDIA_GTM_ID', 'GTM-XXXXXXX' );
 * In locale, per provare il tracciamento:
 *   define( 'LIDIA_FORCE_TRACKING', true );
 *
 * ⚠ Il consent default lo stampa QUESTO file, a wp_head priorità 1, perché deve
 * precedere tutto. Complianz (versione gratuita, 25/09/2026) non ha il Consent Mode v2:
 * anche l'aggiornamento lo fa questo file, ascoltando la scelta fatta nel banner
 * (lidia_ponte_consenso). Nel sorgente della pagina deve comparire un solo
 * gtag('consent','default').
 */

defined( 'ABSPATH' ) || exit;

/**
 * ID del container GTM. Stringa vuota = tracciamento spento.
 */
function lidia_gtm_id(): string {
	return defined( 'LIDIA_GTM_ID' ) ? (string) LIDIA_GTM_ID : '';
}

/**
 * Il tracciamento si accende solo in produzione, e mai per chi lavora sul sito.
 */
function lidia_tracking_enabled(): bool {
	if ( ! lidia_gtm_id() ) {
		return false;
	}

	// Niente tracciamento per redattori e amministratori: inquinerebbe i dati.
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}

	if ( defined( 'LIDIA_FORCE_TRACKING' ) && LIDIA_FORCE_TRACKING ) {
		return true;
	}

	return defined( 'LIDIA_ENV' ) && 'production' === LIDIA_ENV;
}

/**
 * Contesto di pagina spinto nel dataLayer. Alimenta trigger e segmentazioni in GTM.
 *
 * Porta solo ciò che il sito ha davvero: niente funnel_step (il loop è ritirato
 * dall'11/09), niente campaign_slug (il CPT landing arriva in Fase 6).
 * La spec è in tracking/datalayer-spec.md.
 */
function lidia_datalayer_context(): array {
	$id = get_queried_object_id();

	if ( is_front_page() ) {
		$tipo = 'home';
	} elseif ( is_singular() ) {
		$tipo = (string) get_post_type( $id );
	} else {
		$tipo = 'archive';
	}

	$contesto = array(
		'event'         => 'lidia_page_context',
		'page_type'     => $tipo,
		'page_id'       => $id ? (int) $id : 0,
		'page_language' => function_exists( 'pll_current_language' )
			? (string) pll_current_language()
			: substr( get_locale(), 0, 2 ),
		'page_template' => 'site',
		'is_logged_in'  => is_user_logged_in(),
	);

	if ( is_singular( array( 'post', 'risorsa' ) ) ) {
		$contesto['content_title'] = get_the_title( $id );
		$contesto['content_type']  = (string) get_post_type( $id );
	}

	/**
	 * Punto di innesto per la Fase 6: la landing page aggiunge qui
	 * page_template = 'landing' e campaign_slug.
	 */
	return apply_filters( 'lidia_datalayer_context', $contesto, $id );
}

/**
 * In head, a priorità 1, nell'ordine obbligato:
 * 1) consent default (tutto negato)  2) ponte verso il banner  3) contesto  4) container GTM.
 *
 * Se il consent default arriva dopo il CMP, il Consent Mode non funziona e i dati
 * sono inutilizzabili. È l'errore più frequente: l'ordine qui non si tocca.
 */
add_action( 'wp_head', 'lidia_head_tracking', 1 );
function lidia_head_tracking(): void {
	if ( ! lidia_tracking_enabled() ) {
		return;
	}

	$contesto = wp_json_encode( lidia_datalayer_context() );
	$gtm      = lidia_gtm_id();
	?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{
	'ad_storage':'denied',
	'ad_user_data':'denied',
	'ad_personalization':'denied',
	'analytics_storage':'denied',
	'functionality_storage':'granted',
	'security_storage':'granted',
	'wait_for_update':2000
});
gtag('set','ads_data_redaction',true);
gtag('set','url_passthrough',true);
<?php lidia_ponte_consenso(); ?>
dataLayer.push(<?php echo $contesto; /* già passato da wp_json_encode */ ?>);
</script>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
	<?php
}

/**
 * Il ponte fra il banner di Complianz e il Consent Mode v2 (25/09/2026).
 *
 * Complianz, quando il visitatore sceglie — e a ogni pagina, se la scelta è già fatta —
 * lancia sul documento l'evento `cmplz_fire_categories` con le categorie accettate.
 * Qui si traducono in un solo gtag('consent','update'):
 *   statistics → analytics_storage
 *   marketing  → ad_storage, ad_user_data, ad_personalization
 * e si spinge `lidia_consent_update` nel dataLayer, trigger per GTM.
 * `cmplz_revoke` (consenso ritirato da «Preferenze cookie») riporta tutto a negato.
 *
 * Viene stampato dentro lo script del consent default, prima di GTM: l'ascoltatore
 * esiste già quando Complianz, più in basso nella pagina, lancia l'evento.
 */
function lidia_ponte_consenso(): void {
	?>
(function(){
	var ultimo = '';
	function aggiorna(statistiche, marketing){
		var chiave = (statistiche ? 's' : '-') + (marketing ? 'm' : '-');
		if (chiave === ultimo) { return; }
		ultimo = chiave;
		gtag('consent','update',{
			'analytics_storage': statistiche ? 'granted' : 'denied',
			'ad_storage': marketing ? 'granted' : 'denied',
			'ad_user_data': marketing ? 'granted' : 'denied',
			'ad_personalization': marketing ? 'granted' : 'denied'
		});
		dataLayer.push({'event':'lidia_consent_update','consenso_statistiche':statistiche,'consenso_marketing':marketing});
	}
	document.addEventListener('cmplz_fire_categories', function(e){
		var c = (e && e.detail && e.detail.categories) || [];
		aggiorna(c.indexOf('statistics') !== -1, c.indexOf('marketing') !== -1);
	});
	document.addEventListener('cmplz_revoke', function(){ aggiorna(false, false); });
})();
	<?php
}

/**
 * Fallback senza JavaScript. Non misura niente di utile — GTM è JavaScript — ma
 * è parte dello snippet standard e non costa nulla.
 */
add_action( 'wp_body_open', 'lidia_body_tracking', 1 );
function lidia_body_tracking(): void {
	if ( ! lidia_tracking_enabled() ) {
		return;
	}

	printf(
		'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>',
		esc_attr( lidia_gtm_id() )
	);
}
