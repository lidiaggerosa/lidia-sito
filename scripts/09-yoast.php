<?php
/**
 * Yoast: impostazioni del sito e metadati di ogni pagina. Rieseguibile.
 *
 *   wp eval-file scripts/09-yoast.php
 *   wp yoast index --reindex --skip-confirmation   (solo in produzione)
 *
 * Il secondo comando serve: Yoast legge title e description dalla sua tabella degli
 * indexable, non dai campi, e un campo scritto da qui non la aggiorna da solo.
 *
 * Fonte dei testi: il front-matter di content/it/*.md e, per i whitepaper,
 * scripts/blocchi/risorse/manifest.json. Se cambia un testo lì, si cambia anche qui
 * e si rilancia lo script: non si corregge dall'admin (canale unico, CLAUDE.md §6).
 * La home ha il title e la description approvati il 24/09/2026.
 *
 * Le pagine si trovano per percorso, non per ID: gli ID del locale e dello staging
 * non coincidono per forza.
 *
 * @package Lidia
 */

if ( ! defined( 'WPSEO_VERSION' ) || ! class_exists( 'WPSEO_Options' ) ) {
	WP_CLI::error( 'Yoast SEO non è attivo.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/* -------------------------------------------------------------------------
 * 1. Immagini: logo per lo schema e immagine social di default
 *
 * Stanno nel tema (assets/images/) e si importano nella libreria media una volta
 * sola: Yoast le vuole come allegati. Il marcatore `_lidia_asset` rende lo script
 * rieseguibile senza doppioni.
 * ---------------------------------------------------------------------- */

/**
 * Importa un'immagine del tema come allegato, se non c'è già.
 *
 * @param string $file   Percorso relativo al tema.
 * @param string $chiave Marcatore.
 * @param string $titolo Titolo dell'allegato.
 * @param string $alt    Testo alternativo.
 * @return int ID dell'allegato.
 */
function lidia_yoast_allegato( $file, $chiave, $titolo, $alt ) {
	$esistente = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_lidia_asset', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $chiave, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	if ( $esistente ) {
		WP_CLI::log( "= {$chiave}: allegato {$esistente[0]} già presente" );
		return (int) $esistente[0];
	}

	$sorgente = get_theme_file_path( $file );

	if ( ! is_readable( $sorgente ) ) {
		WP_CLI::error( "File mancante nel tema: {$file}" );
	}

	$temporaneo = wp_tempnam( wp_basename( $sorgente ) );
	copy( $sorgente, $temporaneo );

	$id = media_handle_sideload(
		array(
			'name'     => wp_basename( $sorgente ),
			'tmp_name' => $temporaneo,
		),
		0,
		$titolo
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $temporaneo ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		WP_CLI::error( $id->get_error_message() );
	}

	update_post_meta( $id, '_lidia_asset', $chiave );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( "+ {$chiave}: allegato {$id} importato" );

	return (int) $id;
}

$logo   = lidia_yoast_allegato( 'assets/images/logo-lidia-schema.png', 'logo-schema', 'Logo Lidia', 'Lidia' );
$social = lidia_yoast_allegato( 'assets/images/og-lidia.png', 'og-default', 'Lidia — immagine social', 'Lidia' );

/* -------------------------------------------------------------------------
 * 2. Impostazioni del sito
 *
 * I dati societari (P. IVA, indirizzo, LinkedIn) non stanno qui: li aggiunge
 * inc/schema.php, che è nel repo.
 * ---------------------------------------------------------------------- */

$impostazioni = array(
	// Chi rappresenta il sito: senza nome e logo Yoast non stampa l'Organization.
	'company_or_person'           => 'company',
	'company_name'                => 'Lidia',
	'company_logo'                => wp_get_attachment_url( $logo ),
	'company_logo_id'             => $logo,
	'website_name'                => 'Lidia',

	// Separatore dei title costruiti da modello (articoli nuovi): «… | Lidia».
	'separator'                   => 'sc-pipe',

	// Archivi senza valore: spenti o fuori dagli indici, quindi fuori dalla sitemap.
	'disable-author'              => true,
	'disable-date'                => true,
	'disable-post_format'         => true,
	'disable-attachment'          => true,
	'noindex-tax-category'        => true,
	'noindex-tax-post_tag'        => true,
	'noindex-tax-post_format'     => true,

	// Briciole di pane: solo nello schema (BreadcrumbList), nessuna barra visibile.
	'breadcrumbs-enable'          => false,
	'breadcrumbs-home'            => 'Home',

	// Social.
	'opengraph'                   => true,
	'twitter'                     => true,
	'og_default_image'            => wp_get_attachment_url( $social ),
	'og_default_image_id'         => $social,
	'other_social_urls'           => array( 'https://www.linkedin.com/company/lidiatech/' ),

	// Sitemap sì. llms.txt di Yoast no: lo serve il tema (inc/seo.php).
	'enable_xml_sitemap'          => true,
	'enable_llms_txt'             => false,

	// Pulizia dell'head e dei feed che nessuno legge.
	'remove_shortlinks'           => true,
	'remove_rsd_wlw_links'        => true,
	'remove_oembed_links'         => true,
	'remove_generator'            => true,
	'remove_emoji_scripts'        => true,
	'remove_feed_global_comments' => true,
	'remove_feed_post_comments'   => true,
);

foreach ( $impostazioni as $chiave => $valore ) {
	WPSEO_Options::set( $chiave, $valore );
	WP_CLI::log( "  opzione {$chiave}" );
}

/* -------------------------------------------------------------------------
 * 3. Metadati per pagina
 *
 * Mancano apposta: le pagine legali
 * (noindex per costruzione, inc/legale.php), /libreria/ (privata).
 * ---------------------------------------------------------------------- */

$pagine = array(
	'/' => array(
		'title' => 'Lidia — Intelligenza artificiale per avvocati e aziende',
		'desc'  => 'Lidia è l\'intelligenza artificiale legale che lavora con te: ricerca su fonti ufficiali, analisi e redazione documenti, integrata in Word.',
		'kw'    => 'intelligenza artificiale per avvocati',
	),
	'/prodotto/' => array(
		'title' => 'Funzioni di Lidia — software AI per studi legali',
		'desc'  => 'Le funzioni di Lidia, software AI per studi legali: ricerca su fonti ufficiali con GraphRAG, workflow per materia, add-in Word, AI Assistant, Smart Answer, pratiche.',
		'kw'    => 'software intelligenza artificiale studi legali',
	),
	'/sicurezza/' => array(
		'title' => 'Sicurezza dei dati e AI negli studi legali — Lidia',
		'desc'  => 'Dove risiedono i dati, cosa vedono i fornitori dei modelli, quali certificazioni ha Lidia: AWS in Unione Europea, ISO 27001, CSA STAR Level 1, conformità GDPR.',
		'kw'    => 'sicurezza dei dati ai studi legali',
	),
	'/prezzi/' => array(
		'title' => 'Quanto costa un software AI per studi legali — Prezzi Lidia',
		'desc'  => 'Lidia parte da 125 € al mese. Lidia Professional comprende tutte le funzioni, tutte le materie del diritto e l\'accesso alle fonti ufficiali.',
		'kw'    => 'quanto costa un software ai per studi legali',
	),
	'/prova-gratuita/' => array(
		'title' => 'Prova gratuita software legal AI — 7 giorni | Lidia',
		'desc'  => 'Sette giorni di prova gratuita su documenti reali del vostro studio. Tutte le funzioni attive, fonti ufficiali comprese, dati in Unione Europea.',
		'kw'    => 'prova gratuita software legal ai',
	),
	'/azienda/' => array(
		'title' => 'Lidia — Chi progetta l\'AI legale italiana',
		'desc'  => 'Lidia S.r.l. costruisce l\'intelligenza legale che lavora al fianco dello studio. Progettata da avvocati con esperienza nei grandi studi italiani e internazionali.',
		'kw'    => 'lidia legal ai',
	),
	'/azienda/lavora-con-noi/' => array(
		'title' => 'Lavora con noi — Lidia',
		'desc'  => 'Lidia S.r.l., legal tech italiana. Al momento non ci sono posizioni aperte, ma le candidature spontanee si leggono tutte.',
		'kw'    => 'lavora con noi lidia',
	),
	'/contatti/' => array(
		'title' => 'Contatti — Lidia',
		'desc'  => 'Contatti di Lidia S.r.l.: lidia@lidiatech.ai, +39 010 8991141. Sedi di Genova e Milano. Per le prove gratuite usate il form dedicato.',
		'kw'    => 'contatti lidia',
	),
	'/risorse/' => array(
		'title' => 'Risorse su AI e diritto — Lidia',
		'desc'  => 'Paper, analisi e resoconti su come l\'intelligenza artificiale entra nel lavoro legale. Scritti da avvocati, con le fonti in chiaro.',
		'kw'    => '',
	),
	'/risorse/whitepaper/' => array(
		'title' => 'Whitepaper su intelligenza artificiale e diritto — Lidia',
		'desc'  => 'Cinque documenti operativi su governance dei dati, metodo della ricerca giuridica, responsabilità nella filiera dell\'AI e adozione nelle assicurazioni.',
		'kw'    => 'whitepaper intelligenza artificiale diritto',
	),
	'/risorse/articoli/' => array(
		'title' => 'Articoli su AI e diritto — Lidia',
		'desc'  => 'Analisi e resoconti su come l\'intelligenza artificiale cambia il lavoro legale: normativa, metodo, casi. Scritti da avvocati, con le fonti in chiaro.',
		'kw'    => '',
	),
	'/risorse/legge-132-2025-studi-legali/' => array(
		'title' => 'Legge 132/2025 e AI negli studi legali: cosa cambia | Lidia',
		'desc'  => 'La Legge 132/2025 disciplina l\'uso dell\'AI nelle professioni intellettuali: obblighi di trasparenza, supervisione umana, responsabilità e checklist operativa.',
		'kw'    => 'legge 132 2025 intelligenza artificiale',
	),
	'/risorse/agenti-ai-contesto-legale-enterprise/' => array(
		'title' => 'Agenti AI nel contesto legale ed enterprise: cosa cambia | Lidia',
		'desc'  => 'Da assistenti passivi ad agenti autonomi: competenze, AI Act, sovranità digitale e adozione asimmetrica tra grandi imprese e PMI. Il resoconto del panel.',
		'kw'    => 'agenti ai contesto legale',
	),
	'/risorse/whitepaper/odissea-digitale-ai-studi-legali/' => array(
		'title' => 'Odissea digitale: la rotta dell\'AI per gli studi legali | Lidia',
		'desc'  => 'Paper in tre capitoli su come portare l\'AI in produzione in uno studio legale: corpus AI-ready, competenze ibride, policy viva. Download gratuito.',
		'kw'    => 'ai in produzione studi legali',
	),
	'/risorse/whitepaper/opinion-eiopa-governance-ai-assicurazioni/' => array(
		'title' => 'Opinion EIOPA: governance dell\'AI nelle assicurazioni | Lidia',
		'desc'  => 'Lettura operativa dell\'Opinion EIOPA su AI governance e risk management: proporzionalità, impact assessment e selezione degli use case. Download gratuito.',
		'kw'    => 'opinion eiopa intelligenza artificiale',
	),
	'/risorse/whitepaper/nuova-governance-dei-dati/' => array(
		'title' => 'La nuova governance dei dati: impatti legali dell\'AI | Lidia',
		'desc'  => 'I quattro pilastri della governance AI: good data, partire dal problema, compliance by design e test del legittimo interesse per il training. Download gratuito.',
		'kw'    => 'governance dei dati intelligenza artificiale',
	),
	'/risorse/whitepaper/diritto-llm-e-ricerca/' => array(
		'title' => 'Diritto, LLM e ricerca: protocolli e criteri di validità | Lidia',
		'desc'  => 'Quadro teorico, protocolli operativi e criteri di validità per l\'uso degli LLM nella ricerca giuridica. La regola cite-or-silent. Download gratuito.',
		'kw'    => 'llm ricerca giuridica metodo',
	),
	'/risorse/whitepaper/responsabilita-value-chain-ai/' => array(
		'title' => 'La responsabilità nella value chain dell\'AI | Lidia',
		'desc'  => 'Vigilanza umana, cerchi concentrici di responsabilità e diligenza professionale: il panel su chi risponde lungo la catena dell\'AI. Download gratuito.',
		'kw'    => 'responsabilità value chain ai',
	),
);

/**
 * Il contenuto pubblicato a un percorso.
 *
 * @param string $percorso Percorso relativo, con lo slash finale.
 * @return int ID, 0 se non esiste.
 */
function lidia_yoast_trova( $percorso ) {
	if ( '/' === $percorso ) {
		return (int) get_option( 'page_on_front' );
	}

	$pagina = get_page_by_path( trim( $percorso, '/' ), OBJECT, 'page' );

	if ( $pagina ) {
		return (int) $pagina->ID;
	}

	$trovati = get_posts(
		array(
			'name'        => basename( untrailingslashit( $percorso ) ),
			'post_type'   => array( 'post', 'risorsa' ),
			'post_status' => 'publish',
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	return $trovati ? (int) $trovati[0] : 0;
}

/*
 * Nome della pagina nelle briciole di pane (BreadcrumbList): le voci di menu e footer,
 * non l'H1, che su alcune pagine è una frase intera.
 */
$briciole = array(
	'/prodotto/' => 'Prodotto',
	'/sicurezza/' => 'Sicurezza',
	'/prezzi/' => 'Prezzi',
	'/prova-gratuita/' => 'Prova gratuita',
	'/azienda/' => 'Chi siamo',
	'/azienda/lavora-con-noi/' => 'Lavora con noi',
	'/contatti/' => 'Contatti',
	'/risorse/' => 'Risorse',
	'/risorse/whitepaper/' => 'Whitepaper',
	'/risorse/articoli/' => 'Articoli',
);

$mancanti = 0;

foreach ( $pagine as $percorso => $meta ) {
	$id = lidia_yoast_trova( $percorso );

	if ( ! $id ) {
		WP_CLI::warning( "Nessun contenuto a {$percorso}" );
		++$mancanti;
		continue;
	}

	update_post_meta( $id, '_yoast_wpseo_title', $meta['title'] );
	update_post_meta( $id, '_yoast_wpseo_metadesc', $meta['desc'] );

	if ( '' !== $meta['kw'] ) {
		update_post_meta( $id, '_yoast_wpseo_focuskw', $meta['kw'] );
	}

	if ( isset( $briciole[ $percorso ] ) ) {
		update_post_meta( $id, '_yoast_wpseo_bctitle', $briciole[ $percorso ] );
	}

	WP_CLI::log( sprintf( '  %-58s → %d', $percorso, $id ) );
}

if ( $mancanti ) {
	WP_CLI::warning( "{$mancanti} percorsi senza contenuto." );
}

WP_CLI::success( 'Yoast configurato. In produzione: wp yoast index --reindex --skip-confirmation' );
