<?php
/**
 * Stato SEO del sito: title, meta description, robots e opzioni Yoast per ogni contenuto pubblico.
 *
 * Sola lettura. Si lancia con:
 *   wp eval-file scripts/16-seo-stato.php
 *
 * Serve a controllare che nessuna pagina indicizzabile sia senza title o description
 * (gate della Fase 7 in docs/tecnico/seo-e-redirect.md §9) e a leggere le opzioni Yoast
 * senza passare dall'admin.
 *
 * @package Lidia
 */

$tipi = array( 'page', 'risorsa', 'post' );
$righe = get_posts(
	array(
		'post_type'      => $tipi,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

echo "ID | slug | tipo | yoast_title | yoast_metadesc | focuskw | noindex | canonical\n";
foreach ( $righe as $p ) {
	$url = wp_make_link_relative( get_permalink( $p ) );
	printf(
		"%d | %s | %s | %s | %s | %s | %s | %s\n",
		$p->ID,
		$url,
		$p->post_type,
		get_post_meta( $p->ID, '_yoast_wpseo_title', true ) ?: '—',
		get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) ?: '—',
		get_post_meta( $p->ID, '_yoast_wpseo_focuskw', true ) ?: '—',
		get_post_meta( $p->ID, '_yoast_wpseo_meta-robots-noindex', true ) ?: '0',
		get_post_meta( $p->ID, '_yoast_wpseo_canonical', true ) ?: '—'
	);
}

echo "\n--- wpseo_titles ---\n";
$o = get_option( 'wpseo_titles', array() );
foreach ( array(
	'separator', 'title-home-wpseo', 'metadesc-home-wpseo', 'title-page', 'metadesc-page',
	'title-risorsa', 'metadesc-risorsa', 'noindex-risorsa', 'title-ptarchive-risorsa', 'metadesc-ptarchive-risorsa',
	'noindex-ptarchive-risorsa', 'company_or_person', 'company_name', 'company_logo', 'company_logo_id',
	'website_name', 'disable-author', 'disable-date', 'disable-post_format', 'disable-attachment',
	'noindex-author-wpseo', 'noindex-archive-wpseo', 'noindex-tax-post_tag', 'noindex-tax-category',
	'breadcrumbs-enable', 'breadcrumbs-home',
) as $k ) {
	echo $k, ' = ', var_export( $o[ $k ] ?? null, true ), "\n";
}

echo "\n--- wpseo_social ---\n";
$s = get_option( 'wpseo_social', array() );
foreach ( array( 'og_default_image', 'og_default_image_id', 'linkedin_url', 'twitter_site', 'opengraph', 'twitter' ) as $k ) {
	echo $k, ' = ', var_export( $s[ $k ] ?? null, true ), "\n";
}

echo "\n--- wpseo ---\n";
$w = get_option( 'wpseo', array() );
foreach ( array( 'enable_xml_sitemap', 'enable_enhanced_slack_sharing', 'remove_feed_global', 'remove_feed_global_comments', 'remove_shortlinks', 'remove_rest_api_links', 'remove_rsd_wlw_links', 'remove_oembed_links', 'remove_generator', 'remove_emoji_scripts', 'enable_llms_txt' ) as $k ) {
	echo $k, ' = ', var_export( $w[ $k ] ?? null, true ), "\n";
}

echo "\n--- sito ---\n";
echo 'home = ', get_option( 'home' ), "\n";
echo 'blogname = ', get_option( 'blogname' ), "\n";
echo 'blogdescription = ', get_option( 'blogdescription' ), "\n";
echo 'blog_public = ', get_option( 'blog_public' ), "\n";
echo 'permalink_structure = ', get_option( 'permalink_structure' ), "\n";
echo 'page_on_front = ', get_option( 'page_on_front' ), "\n";
