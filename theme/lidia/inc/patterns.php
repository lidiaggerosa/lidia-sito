<?php
/**
 * Categorie dei pattern di Lidia.
 *
 * I pattern core e quelli remoti sono già spenti in setup.php: nell'inserter
 * il team marketing vede solo i pattern del design system.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le sezioni della home, le composizioni generiche, i gesti in archivio e
 * l'ossatura dei contenuti nuovi.
 */
function lidia_register_pattern_categories() {
	register_block_pattern_category(
		'lidia-home',
		array(
			'label'       => __( 'Lidia — sezioni della home', 'lidia' ),
			'description' => __( 'Le nove sezioni della home approvata il 16/09/2026. Si riusano sulle altre pagine, nell\'ordine che serve.', 'lidia' ),
		)
	);

	register_block_pattern_category(
		'lidia-sezioni',
		array(
			'label' => __( 'Lidia — sezioni', 'lidia' ),
		)
	);

	/*
	 * I pattern di partenza per chi scrive: l'ossatura di un whitepaper e di un
	 * articolo. Non contengono testo da pubblicare, solo la struttura.
	 */
	register_block_pattern_category(
		'lidia-contenuti',
		array(
			'label'       => __( 'Lidia — contenuti', 'lidia' ),
			'description' => __( 'L\'ossatura di un whitepaper e di un articolo. Si inserisce in un contenuto vuoto e si riempie: le righe fra parentesi quadre si cancellano scrivendo.', 'lidia' ),
		)
	);

	/*
	 * I tre gesti del concept del 15/09 restano registrati come categoria ma
	 * fuori dall'inseritore (`Inserter: no` nei singoli pattern): tenuti da parte
	 * per le pagine interne, non in uso sulla home.
	 */
	register_block_pattern_category(
		'lidia-gesti',
		array(
			'label'       => __( 'Lidia — gesti (archivio)', 'lidia' ),
			'description' => __( 'Riscontro, coppia domanda → ragionamento, fatto isolato. Fuori uso dal 16/09/2026: prima di riprenderli vanno riportati sui token della Palette D.', 'lidia' ),
		)
	);
}
add_action( 'init', 'lidia_register_pattern_categories' );
