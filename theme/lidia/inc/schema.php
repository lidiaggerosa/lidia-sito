<?php
/**
 * JSON-LD che Yoast non copre.
 *
 * Yoast costruisce il grafo (WebSite, Organization, WebPage, BreadcrumbList, Article):
 * qui lo si completa, non lo si duplica (docs/06-seo-technical.md §4).
 *
 * - Organization: ragione sociale, P. IVA, sede legale, contatti, profili. Nessun riferimento
 *   al gruppo (decisione del 30/09, applicata il 07/10).
 * - FAQPage: su ogni pagina con una sezione `lidia-faq`, costruito leggendo le domande
 *   della pagina. Il testo dello schema è quello visibile per costruzione: non esiste una
 *   seconda copia da tenere allineata, e una FAQ nuova entra nello schema da sola.
 * - SoftwareApplication: su /prodotto/ e /prezzi/ (IT ed EN), con il prezzo di partenza già pubblico.
 * - Article sui whitepaper (CPT `risorsa`), con gli autori del campo `lidia_autori`.
 * - Briciole: Home › Risorse › Articoli|Whitepaper › titolo, anche nella BreadcrumbList.
 * - Articoli: autore Lidia (l'Organization), non l'utente WordPress che li ha caricati.
 *
 * Tutto passa dai filtri di Yoast: se Yoast è spento, questo file non stampa niente.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Organization
 * ---------------------------------------------------------------------- */

/**
 * Dati societari. Gli stessi del footer e di /contatti/ (decision log 17/09).
 *
 * Stanno qui e non nei campi «Rappresentazione del sito» di Yoast: un campo vive nel
 * database, questo file nel repo.
 *
 * @param array $dati Nodo Organization di Yoast.
 * @return array
 */
function lidia_schema_organizzazione( $dati ) {
	$dati['legalName'] = 'Lidia S.r.l.';
	$dati['vatID']     = 'IT02976860995';
	$dati['email']     = 'lidia@lidiatech.ai';
	$dati['telephone'] = '+39 010 8991141';
	$dati['address']   = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Corso Andrea Podestà 8/3',
		'postalCode'      => '16128',
		'addressLocality' => 'Genova',
		'addressRegion'   => 'GE',
		'addressCountry'  => 'IT',
	);

	// Nomi con cui il brand viene cercato (query di Search Console, 07/10/2026): aiutano a
	// distinguere Lidia dalle omonime.
	$dati['alternateName'] = array( 'Lidia AI', 'LidiaTech' );

	// I profili ufficiali stanno qui, non nei campi social di Yoast (database).
	$profili        = isset( $dati['sameAs'] ) ? (array) $dati['sameAs'] : array();
	$profili        = array_merge( $profili, lidia_profili_ufficiali() );
	$dati['sameAs'] = array_values( array_unique( $profili ) );

	return $dati;
}
add_filter( 'wpseo_schema_organization', 'lidia_schema_organizzazione' );

/**
 * Profili ufficiali di Lidia, confermati dall'owner il 07/10/2026.
 *
 * @return string[]
 */
function lidia_profili_ufficiali() {
	return array(
		'https://www.linkedin.com/company/lidiatech/',
		'https://www.facebook.com/profile.php?id=61586216139896',
		'https://www.youtube.com/@LIDIA_TechAI',
		'https://marketplace.microsoft.com/en-us/product/web-apps/mesa.lidia_nocosell',
		'https://www.hublegaltech.com/glth-members-2025/lidia',
		'https://www.instagram.com/lidiatech.ai/',
	);
}

/**
 * Anche il nodo WebSite porta i nomi alternativi: è quello che Google legge per il nome
 * del sito nei risultati.
 *
 * @param array $dati Nodo WebSite di Yoast.
 * @return array
 */
function lidia_schema_sito( $dati ) {
	$dati['alternateName'] = array( 'Lidia AI', 'LidiaTech' );

	return $dati;
}
add_filter( 'wpseo_schema_website', 'lidia_schema_sito' );

/* -------------------------------------------------------------------------
 * FAQPage
 * ---------------------------------------------------------------------- */

/**
 * HTML di un blocco statico, senza passare dal rendering.
 *
 * Le FAQ sono paragrafi ed elenchi: basta ricomporre `innerContent`, e si evita di far
 * girare i filtri di rendering (e i loro effetti) dentro l'head.
 *
 * @param array $blocco Blocco da parse_blocks().
 * @return string
 */
function lidia_schema_html_blocco( $blocco ) {
	$html   = '';
	$indice = 0;

	foreach ( (array) $blocco['innerContent'] as $pezzo ) {
		if ( is_string( $pezzo ) ) {
			$html .= $pezzo;
			continue;
		}

		if ( isset( $blocco['innerBlocks'][ $indice ] ) ) {
			$html .= lidia_schema_html_blocco( $blocco['innerBlocks'][ $indice ] );
		}
		++$indice;
	}

	return $html;
}

/**
 * Testo piano da un frammento HTML: niente tag, entità decodificate, spazi normalizzati.
 *
 * @param string $html Frammento.
 * @return string
 */
function lidia_schema_testo( $html ) {
	$testo = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	return trim( preg_replace( '/\s+/u', ' ', $testo ) );
}

/**
 * Raccoglie le domande dentro le sezioni `lidia-faq`.
 *
 * Solo i blocchi Dettagli dentro una sezione FAQ: altri Dettagli nella pagina non sono
 * domande frequenti e non entrano nello schema. Un riferimento a un pattern
 * (`core/pattern`) si apre e si legge come il resto.
 *
 * @param array $blocchi    Blocchi.
 * @param bool  $in_faq     Siamo già dentro una sezione FAQ.
 * @param array $domande    Accumulatore: coppie [domanda, risposta].
 * @param int   $profondita Guardia contro i cicli.
 */
function lidia_schema_cerca_faq( $blocchi, $in_faq, &$domande, $profondita = 0 ) {
	if ( $profondita > 12 ) {
		return;
	}

	foreach ( $blocchi as $blocco ) {
		$nome = isset( $blocco['blockName'] ) ? $blocco['blockName'] : '';

		if ( 'core/pattern' === $nome && ! empty( $blocco['attrs']['slug'] ) ) {
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $blocco['attrs']['slug'] );

			if ( $pattern && ! empty( $pattern['content'] ) ) {
				lidia_schema_cerca_faq( parse_blocks( $pattern['content'] ), $in_faq, $domande, $profondita + 1 );
			}
			continue;
		}

		$classi = isset( $blocco['attrs']['className'] ) ? (string) $blocco['attrs']['className'] : '';
		$qui    = $in_faq || (bool) preg_match( '/(^|\s)lidia-faq(\s|$)/', $classi );

		if ( $qui && 'core/details' === $nome ) {
			if ( preg_match( '#<summary[^>]*>(.*?)</summary>#s', (string) $blocco['innerHTML'], $trovato ) ) {
				$risposta = '';

				foreach ( (array) $blocco['innerBlocks'] as $interno ) {
					$risposta .= lidia_schema_html_blocco( $interno ) . ' ';
				}

				$domanda  = lidia_schema_testo( $trovato[1] );
				$risposta = lidia_schema_testo( $risposta );

				if ( '' !== $domanda && '' !== $risposta ) {
					$domande[] = array( $domanda, $risposta );
				}
			}
			continue;
		}

		if ( ! empty( $blocco['innerBlocks'] ) ) {
			lidia_schema_cerca_faq( $blocco['innerBlocks'], $qui, $domande, $profondita + 1 );
		}
	}
}

/**
 * Le domande frequenti di un contenuto.
 *
 * @param int $post_id ID.
 * @return array Coppie [domanda, risposta].
 */
function lidia_schema_domande( $post_id ) {
	static $cache = array();

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$post    = get_post( $post_id );
	$domande = array();

	if ( $post && '' !== $post->post_content ) {
		lidia_schema_cerca_faq( parse_blocks( $post->post_content ), false, $domande );
	}

	$cache[ $post_id ] = $domande;

	return $domande;
}

/**
 * La pagina con FAQ diventa anche FAQPage.
 *
 * È il modo in cui Yoast stesso marca le FAQ: il nodo WebPage prende il tipo
 * aggiuntivo e le domande in `mainEntity`. Nessun secondo nodo, nessun duplicato.
 *
 * @param array $dati Nodo WebPage di Yoast.
 * @return array
 */
function lidia_schema_faq( $dati ) {
	if ( ! is_singular() ) {
		return $dati;
	}

	$domande = lidia_schema_domande( get_queried_object_id() );

	if ( ! $domande ) {
		return $dati;
	}

	$tipi = isset( $dati['@type'] ) ? (array) $dati['@type'] : array( 'WebPage' );

	if ( ! in_array( 'FAQPage', $tipi, true ) ) {
		$tipi[] = 'FAQPage';
	}

	$dati['@type']      = $tipi;
	$dati['mainEntity'] = array();

	foreach ( $domande as $coppia ) {
		$dati['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => $coppia[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $coppia[1],
			),
		);
	}

	return $dati;
}
add_filter( 'wpseo_schema_webpage', 'lidia_schema_faq' );

/* -------------------------------------------------------------------------
 * SoftwareApplication
 * ---------------------------------------------------------------------- */

/**
 * Il prodotto, su /prodotto/ e /prezzi/ e sulle due pagine inglesi corrispondenti.
 *
 * Il prezzo è quello pubblico, «da 125 € al mese» (decision log 14/09): nessun altro
 * dato del listino. Nessuna valutazione aggregata: non ce ne sono di pubbliche, e una
 * inventata è esattamente ciò che lo schema non deve contenere.
 *
 * 07/10/2026: anche su /prezzi/, /en/product/ e /en/pricing/: il prezzo si legge
 * soprattutto in /prezzi/. Stesso @id ovunque, perché è lo stesso prodotto.
 *
 * @param array  $grafo    Nodi del grafo Yoast.
 * @param object $contesto Meta_Tags_Context di Yoast.
 * @return array
 */
function lidia_schema_software( $grafo, $contesto ) {
	if ( ! is_page( array( 'prodotto', 'prezzi', 'product', 'pricing' ) ) ) {
		return $grafo;
	}

	$sito    = isset( $contesto->site_url ) ? $contesto->site_url : trailingslashit( home_url() );
	$pagina  = get_queried_object_id();
	$testo   = (string) get_post_meta( $pagina, '_yoast_wpseo_metadesc', true );
	$inglese = function_exists( 'pll_get_post_language' ) && 'en' === pll_get_post_language( $pagina );

	$nodo = array(
		'@type'                  => 'SoftwareApplication',
		'@id'                    => $sito . '#software',
		'name'                   => 'Lidia',
		'url'                    => get_permalink( $pagina ),
		'applicationCategory'    => 'BusinessApplication',
		'applicationSubCategory' => $inglese ? 'Legal artificial intelligence' : 'Intelligenza artificiale legale',
		'operatingSystem'        => 'Web',
		'inLanguage'             => $inglese ? 'en' : 'it-IT',
		'featureList'            => $inglese
			? array(
				'Legal research on official sources (GraphRAG)',
				'Lidia Workflow',
				'Word add-in',
				'AI Assistant',
				'Smart Answer',
				'Workflow Builder',
				'Matters',
				'Advanced OCR',
			)
			: array(
				'Ricerca legale su fonti ufficiali (GraphRAG)',
				'Lidia Workflow',
				'Add-in per Word',
				'AI Assistant',
				'Smart Answer',
				'Workflow Builder',
				'Pratiche',
				'OCR avanzato',
			),
		'publisher'              => array( '@id' => $sito . '#organization' ),
		'offers'                 => array(
			'@type'              => 'Offer',
			'price'              => '125',
			'priceCurrency'      => 'EUR',
			'url'                => home_url( $inglese ? '/en/pricing/' : '/prezzi/' ),
			'priceSpecification' => array(
				'@type'             => 'UnitPriceSpecification',
				'price'             => '125',
				'priceCurrency'     => 'EUR',
				'referenceQuantity' => array(
					'@type'    => 'QuantitativeValue',
					'value'    => 1,
					'unitCode' => 'MON',
				),
			),
		),
	);

	if ( '' !== $testo ) {
		$nodo['description'] = $testo;
	}

	$grafo[] = $nodo;

	return $grafo;
}
add_filter( 'wpseo_schema_graph', 'lidia_schema_software', 10, 2 );

/* -------------------------------------------------------------------------
 * Article sui whitepaper
 * ---------------------------------------------------------------------- */

/**
 * Un whitepaper è un documento con autori e data: Yoast lo marca solo come WebPage.
 *
 * Gli autori vengono da `lidia_autori`, gli stessi mostrati in pagina da
 * `single-risorsa.html`. Senza autori il nodo non si aggiunge: meglio nessun Article
 * che uno con l'autore sbagliato. (07/10/2026)
 *
 * @param array  $grafo    Nodi del grafo Yoast.
 * @param object $contesto Meta_Tags_Context di Yoast.
 * @return array
 */
function lidia_schema_whitepaper( $grafo, $contesto ) {
	if ( ! is_singular( 'risorsa' ) ) {
		return $grafo;
	}

	$id     = get_queried_object_id();
	$autori = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $id, 'lidia_autori', true ) ) ) );

	if ( ! $autori ) {
		return $grafo;
	}

	$sito   = isset( $contesto->site_url ) ? $contesto->site_url : trailingslashit( home_url() );
	$pagina = get_permalink( $id );
	$testo  = (string) get_post_meta( $id, '_yoast_wpseo_metadesc', true );

	$nodo = array(
		'@type'            => 'Article',
		'@id'              => $pagina . '#article',
		'isPartOf'         => array( '@id' => $pagina ),
		'mainEntityOfPage' => array( '@id' => $pagina ),
		'headline'         => wp_strip_all_tags( html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) ),
		'datePublished'    => get_the_date( 'c', $id ),
		'dateModified'     => get_the_modified_date( 'c', $id ),
		'articleSection'   => 'Whitepaper',
		'inLanguage'       => 'it-IT',
		'publisher'        => array( '@id' => $sito . '#organization' ),
		'author'           => array_map(
			function ( $nome ) {
				return array(
					'@type' => 'Person',
					'name'  => $nome,
				);
			},
			array_values( $autori )
		),
	);

	if ( '' !== $testo ) {
		$nodo['description'] = $testo;
	}

	$grafo[] = $nodo;

	return $grafo;
}
add_filter( 'wpseo_schema_graph', 'lidia_schema_whitepaper', 10, 2 );

/* -------------------------------------------------------------------------
 * Briciole
 * ---------------------------------------------------------------------- */

/**
 * Articoli e whitepaper stanno sotto /risorse/: le briciole lo dicono.
 *
 * Senza filtro Yoast scrive Home › titolo. Il filtro vale anche per la BreadcrumbList
 * dello schema, che Yoast costruisce dalle stesse briciole. (07/10/2026)
 *
 * @param array $briciole Briciole di Yoast.
 * @return array
 */
function lidia_briciole( $briciole ) {
	if ( is_singular( 'post' ) ) {
		$percorsi = array( 'risorse', 'risorse/articoli' );
	} elseif ( is_singular( 'risorsa' ) ) {
		$percorsi = array( 'risorse', 'risorse/whitepaper' );
	} else {
		return $briciole;
	}

	$intermedie = array();

	foreach ( $percorsi as $percorso ) {
		$pagina = get_page_by_path( $percorso );

		if ( $pagina && 'publish' === $pagina->post_status ) {
			$nome         = (string) get_post_meta( $pagina->ID, '_yoast_wpseo_bctitle', true );
			$intermedie[] = array(
				'url'  => get_permalink( $pagina ),
				'text' => '' !== $nome ? $nome : get_the_title( $pagina ),
			);
		}
	}

	if ( ! $intermedie || count( $briciole ) < 2 ) {
		return $briciole;
	}

	// Dopo la home, prima del contenuto.
	array_splice( $briciole, 1, count( $briciole ) - 2, $intermedie );

	return $briciole;
}
add_filter( 'wpseo_breadcrumb_links', 'lidia_briciole' );

/* -------------------------------------------------------------------------
 * Autore degli articoli
 * ---------------------------------------------------------------------- */

/**
 * Gli articoli sono firmati Lidia: l'autore nello schema è l'Organization, non l'utente
 * WordPress che li ha caricati (decisione dell'owner del 07/10/2026). I whitepaper hanno
 * invece autori con nome, da `lidia_autori`.
 *
 * @param array $dati Nodo Article di Yoast.
 * @return array
 */
function lidia_schema_autore_articolo( $dati ) {
	if ( is_singular( 'post' ) ) {
		$dati['author'] = array( '@id' => trailingslashit( home_url() ) . '#organization' );
	}

	return $dati;
}
add_filter( 'wpseo_schema_article', 'lidia_schema_autore_articolo' );

/**
 * Senza autore persona, il nodo Person dell'utente WordPress non serve: si toglie.
 *
 * @param array $pezzi Generatori del grafo Yoast.
 * @return array
 */
function lidia_schema_senza_persona( $pezzi ) {
	if ( ! is_singular( 'post' ) ) {
		return $pezzi;
	}

	return array_values(
		array_filter(
			$pezzi,
			function ( $pezzo ) {
				return ! ( $pezzo instanceof \Yoast\WP\SEO\Generators\Schema\Author );
			}
		)
	);
}
add_filter( 'wpseo_schema_graph_pieces', 'lidia_schema_senza_persona', 20 );
