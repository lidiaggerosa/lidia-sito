<?php
/**
 * Font, CSS di base e CSS per sezione.
 *
 * Regola del progetto: il CSS di una sezione si carica solo sulle pagine in cui
 * quella sezione compare. Il controllo è sul contenuto della pagina, che è dove
 * vivono i pattern: se la classe non c'è, il foglio non parte.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Numero di versione di un file del tema, per il cache busting.
 *
 * @param string $percorso Percorso relativo alla radice del tema.
 * @return string
 */
function lidia_versione_file( $percorso ) {
	$assoluto = LIDIA_DIR . '/' . ltrim( $percorso, '/' );
	return is_readable( $assoluto ) ? (string) filemtime( $assoluto ) : LIDIA_VERSION;
}

/**
 * Elenco delle sezioni: stringa da cercare nel contenuto => foglio di stile.
 *
 * Quasi sempre la stringa è una classe. Per il modulo è il nome del blocco, perché
 * `lidia/modulo` è un blocco dinamico e nel contenuto non c'è nessuna classe da cercare.
 *
 * @return array<string, string>
 */
function lidia_sezioni() {
	return array(
		'lidia-hero'       => 'assets/css/sezioni/hero.css',
		'lidia-loghi'      => 'assets/css/sezioni/loghi.css',
		'lidia-lettura'    => 'assets/css/sezioni/lettura.css',
		'lidia-funzioni'   => 'assets/css/sezioni/funzioni.css',
		'lidia-come'       => 'assets/css/sezioni/come-funziona.css',
		'lidia-sicurezza'  => 'assets/css/sezioni/sicurezza.css',
		'lidia-prezzo'     => 'assets/css/sezioni/prezzo.css',
		'lidia-faq'        => 'assets/css/sezioni/faq.css',
		'lidia-form'       => 'assets/css/sezioni/form.css',
		'lidia-prodotto'   => 'assets/css/sezioni/prodotto.css',
		'lidia-fiduciario' => 'assets/css/sezioni/fiduciario.css',
		'lidia-prezzi'     => 'assets/css/sezioni/prezzi.css',
		'lidia-risorse'    => 'assets/css/sezioni/risorse.css',
		'lidia-prova'      => 'assets/css/sezioni/prova.css',
		'lidia-contatti'   => 'assets/css/sezioni/contatti.css',
		'lidia-azienda'    => 'assets/css/sezioni/azienda.css',
		'lidia-funzione-box' => 'assets/css/sezioni/funzione.css',
		'wp:lidia/modulo'  => 'assets/css/sezioni/modulo.css',
	);
}

/**
 * Elenco dei componenti: classe da cercare => foglio di stile.
 *
 * Stessa regola delle sezioni, applicata ai gesti del concept: si caricano solo
 * dove il gesto compare davvero.
 *
 * @return array<string, string>
 */
function lidia_componenti() {
	return array(
		'lidia-coppia' => 'assets/css/components/coppia.css',
		'lidia-fatto'  => 'assets/css/components/fatto-isolato.css',
		// Gli idiomi delle demo (fonti, passi, documenti, doc, risposta): li usano la fila
		// delle funzioni della home e il box funzione. Nel contenuto c'è sempre una classe
		// `lidia-demo*` (la nota «esempio»); il box funzione lo carica comunque, sotto.
		'lidia-demo'   => 'assets/css/components/demo.css',
	);
}

/**
 * tokens.css, base.css e i due pezzi di cornice: valgono ovunque.
 */
function lidia_enqueue_base() {
	wp_enqueue_style(
		'lidia-tokens',
		LIDIA_URI . '/assets/css/tokens.css',
		array(),
		lidia_versione_file( 'assets/css/tokens.css' )
	);

	wp_enqueue_style(
		'lidia-base',
		LIDIA_URI . '/assets/css/base.css',
		array( 'lidia-tokens' ),
		lidia_versione_file( 'assets/css/base.css' )
	);

	// Lo script del modulo gira su tutte le pagine, non solo dove c'è il modulo: al primo
	// arrivo legge i parametri di campagna dall'indirizzo e li tiene per la sessione, così
	// chi atterra sulla home e compila su /prova-gratuita/ arriva in Delera con la campagna.
	// Pesa meno di 5 KB e non tocca il DOM se non trova moduli.
	wp_enqueue_script( 'lidia-modulo' );

	// Gli eventi del sito per il dataLayer (tracking/datalayer-spec.md §3): su tutte le pagine,
	// dopo il contenuto. Spinge sempre; cosa arriva ai pixel lo decidono GTM e il consenso.
	wp_enqueue_script(
		'lidia-eventi',
		LIDIA_URI . '/assets/js/tracking.js',
		array(),
		lidia_versione_file( 'assets/js/tracking.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	foreach ( array( 'testata', 'piede' ) as $parte ) {
		wp_enqueue_style(
			'lidia-' . $parte,
			LIDIA_URI . '/assets/css/parti/' . $parte . '.css',
			array( 'lidia-tokens' ),
			lidia_versione_file( 'assets/css/parti/' . $parte . '.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lidia_enqueue_base' );

/**
 * Cornice delle pagine non composte da sezioni.
 *
 * La home è fatta di pattern e non ne ha bisogno. Tutto il resto — pagine di
 * testo, articoli, archivi, ricerca, 404 — condivide intestazione e prosa;
 * gli elenchi aggiungono schede e paginazione solo dove servono.
 */
function lidia_enqueue_cornice_pagina() {
	if ( is_front_page() ) {
		return;
	}

	wp_enqueue_style(
		'lidia-pagina',
		LIDIA_URI . '/assets/css/parti/pagina.css',
		array( 'lidia-tokens', 'lidia-base' ),
		lidia_versione_file( 'assets/css/parti/pagina.css' )
	);

	if ( is_home() || is_archive() || is_search() ) {
		wp_enqueue_style(
			'lidia-listato',
			LIDIA_URI . '/assets/css/parti/listato.css',
			array( 'lidia-tokens', 'lidia-base', 'lidia-pagina' ),
			lidia_versione_file( 'assets/css/parti/listato.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lidia_enqueue_cornice_pagina' );

/**
 * CSS delle sezioni presenti nella pagina richiesta.
 */
function lidia_enqueue_sezioni() {
	$oggetto = get_queried_object();

	if ( ! $oggetto instanceof WP_Post ) {
		return;
	}

	$contenuto = $oggetto->post_content;

	// La libreria mostra tutte le sezioni: carica tutto, più il suo foglio.
	$libreria = false !== strpos( $contenuto, 'wp:lidia/libreria' );

	if ( $libreria ) {
		wp_enqueue_style(
			'lidia-libreria',
			LIDIA_URI . '/assets/css/sezioni/libreria.css',
			array( 'lidia-tokens', 'lidia-base' ),
			lidia_versione_file( 'assets/css/sezioni/libreria.css' )
		);
	}

	foreach ( lidia_sezioni() + lidia_componenti() as $cerca => $foglio ) {
		if ( ! $libreria && false === strpos( $contenuto, $cerca ) ) {
			continue;
		}

		wp_enqueue_style(
			'lidia-' . str_replace( array( 'wp:', '/' ), array( '', '-' ), $cerca ),
			LIDIA_URI . '/' . $foglio,
			array( 'lidia-tokens', 'lidia-base' ),
			lidia_versione_file( $foglio )
		);
	}

	// Il box funzione e la fila delle funzioni usano gli idiomi delle demo anche se nel
	// contenuto nessuna classe `lidia-demo*` è rimasta: il foglio parte comunque (24/09).
	if ( $libreria || false !== strpos( $contenuto, 'lidia-funzione-box' ) || false !== strpos( $contenuto, 'lidia-funzioni' ) ) {
		wp_enqueue_style(
			'lidia-lidia-demo',
			LIDIA_URI . '/assets/css/components/demo.css',
			array( 'lidia-tokens', 'lidia-base' ),
			lidia_versione_file( 'assets/css/components/demo.css' )
		);
	}

	// La fila delle funzioni: frecce e trascinamento al posto della barra (23/09).
	// Solo dove la sezione c'è; ~2 KB, nessuna dipendenza, in fondo alla pagina.
	if ( $libreria || false !== strpos( $contenuto, 'lidia-funzioni' ) ) {
		wp_enqueue_script(
			'lidia-funzioni',
			LIDIA_URI . '/assets/js/funzioni.js',
			array(),
			lidia_versione_file( 'assets/js/funzioni.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lidia_enqueue_sezioni', 11 );

/*
 * Il riscontro (gesto 1) resta fuori uso dal 16/09: foglio e pattern in archivio,
 * non caricati. Coppia e fatto isolato sono stati ripresi il 16/09 per le pagine
 * interne e sono nella mappa dei componenti qui sopra.
 */

/**
 * Stili di blocco: le varianti che l'editor deve poter scegliere per nome.
 */
function lidia_register_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'secondario',
			'label' => __( 'Secondario', 'lidia' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'terziario',
			'label' => __( 'Terziario', 'lidia' ),
		)
	);
}
add_action( 'init', 'lidia_register_block_styles' );

/**
 * Preload dei due tondi: sono i pesi sopra la piega.
 */
function lidia_preload_font() {
	$font = array(
		'/assets/fonts/inter-variable.woff2',
		'/assets/fonts/fraunces-variable.woff2',
	);

	foreach ( $font as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( LIDIA_URI . $file )
		);
	}
}
add_action( 'wp_head', 'lidia_preload_font', 1 );

/**
 * Stili dell'editor: l'editor deve mostrare quello che vede il visitatore.
 */
function lidia_editor_styles() {
	add_editor_style(
		array(
			'assets/css/tokens.css',
			'assets/css/base.css',
			'assets/css/parti/testata.css',
			'assets/css/parti/piede.css',
			'assets/css/parti/pagina.css',
			'assets/css/parti/listato.css',
			'assets/css/sezioni/hero.css',
			'assets/css/sezioni/loghi.css',
			'assets/css/sezioni/lettura.css',
			'assets/css/sezioni/funzioni.css',
			'assets/css/sezioni/come-funziona.css',
			'assets/css/sezioni/sicurezza.css',
			'assets/css/sezioni/prezzo.css',
			'assets/css/sezioni/faq.css',
			'assets/css/sezioni/form.css',
			'assets/css/sezioni/prodotto.css',
			'assets/css/sezioni/fiduciario.css',
			'assets/css/sezioni/prezzi.css',
			'assets/css/sezioni/risorse.css',
			'assets/css/sezioni/prova.css',
			'assets/css/sezioni/contatti.css',
			'assets/css/sezioni/azienda.css',
			'assets/css/sezioni/modulo.css',
			'assets/css/sezioni/funzione.css',
			'assets/css/components/coppia.css',
			'assets/css/components/fatto-isolato.css',
			'assets/css/components/demo.css',
		)
	);
}
add_action( 'after_setup_theme', 'lidia_editor_styles' );

/**
 * Il banner dei cookie (Complianz) ridisegnato nel design system.
 *
 * Solo se Complianz è attivo; priorità alta, così arriva dopo il suo foglio.
 */
function lidia_enqueue_consenso() {
	if ( ! defined( 'CMPLZ_VERSION' ) ) {
		return;
	}

	wp_enqueue_style(
		'lidia-consenso',
		LIDIA_URI . '/assets/css/components/consenso.css',
		array( 'lidia-tokens' ),
		lidia_versione_file( 'assets/css/components/consenso.css' )
	);

	// «Preferenze cookie» nel footer riapre il banner (Complianz free lo apre
	// solo dal suo bottone «Gestisci consenso»).
	wp_enqueue_script(
		'lidia-consenso',
		LIDIA_URI . '/assets/js/consenso.js',
		array(),
		lidia_versione_file( 'assets/js/consenso.js' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
}
add_action( 'wp_enqueue_scripts', 'lidia_enqueue_consenso', 100 );
