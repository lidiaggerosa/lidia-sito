<?php
/**
 * JSON-LD che Yoast non copre.
 *
 * Yoast costruisce il grafo (WebSite, Organization, WebPage, BreadcrumbList, Article):
 * qui lo si completa, non lo si duplica (docs/06-seo-technical.md §4).
 *
 * - Organization: ragione sociale, P. IVA, sede legale, contatti, LinkedIn, gruppo.
 * - FAQPage: su ogni pagina con una sezione `lidia-faq`, costruito leggendo le domande
 *   della pagina. Il testo dello schema è quello visibile per costruzione: non esiste una
 *   seconda copia da tenere allineata, e una FAQ nuova entra nello schema da sola.
 * - SoftwareApplication: su /prodotto/, con il prezzo di partenza già pubblico.
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

	$dati['parentOrganization'] = array(
		'@type' => 'Organization',
		'name'  => 'Gruppo MESA',
	);

	$profili        = isset( $dati['sameAs'] ) ? (array) $dati['sameAs'] : array();
	$profili[]      = 'https://www.linkedin.com/company/lidiatech/';
	$dati['sameAs'] = array_values( array_unique( $profili ) );

	return $dati;
}
add_filter( 'wpseo_schema_organization', 'lidia_schema_organizzazione' );

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
 * Il prodotto, su /prodotto/.
 *
 * Il prezzo è quello pubblico, «da 125 € al mese» (decision log 14/09): nessun altro
 * dato del listino. Nessuna valutazione aggregata: non ce ne sono di pubbliche, e una
 * inventata è esattamente ciò che lo schema non deve contenere.
 *
 * @param array  $grafo    Nodi del grafo Yoast.
 * @param object $contesto Meta_Tags_Context di Yoast.
 * @return array
 */
function lidia_schema_software( $grafo, $contesto ) {
	if ( ! is_page( 'prodotto' ) ) {
		return $grafo;
	}

	$sito   = isset( $contesto->site_url ) ? $contesto->site_url : trailingslashit( home_url() );
	$pagina = get_queried_object_id();
	$testo  = (string) get_post_meta( $pagina, '_yoast_wpseo_metadesc', true );

	$nodo = array(
		'@type'                  => 'SoftwareApplication',
		'@id'                    => $sito . '#software',
		'name'                   => 'Lidia',
		'url'                    => get_permalink( $pagina ),
		'applicationCategory'    => 'BusinessApplication',
		'applicationSubCategory' => 'Intelligenza artificiale legale',
		'operatingSystem'        => 'Web',
		'inLanguage'             => 'it-IT',
		'featureList'            => array(
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
			'url'                => home_url( '/prezzi/' ),
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
