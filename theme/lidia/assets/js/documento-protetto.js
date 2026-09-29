/**
 * Pannello «Documento protetto» — scelta del PDF di un whitepaper.
 *
 * Apre la libreria media dalla pagina del whitepaper: WordPress passa l'ID del
 * contenuto al caricamento e il tema instrada il file in uploads/riservati/
 * (inc/delera.php). Qui si aggiorna solo il campo nascosto e ciò che si vede;
 * il salvataggio lo fa il form del post (inc/meta.php).
 *
 * Niente build: JavaScript semplice, come tutto il resto del tema.
 */
( function ( wp, testi ) {
	'use strict';

	var pannello = document.querySelector( '.lidia-documento' );

	if ( ! pannello || ! wp || ! wp.media ) {
		return;
	}

	var campo     = pannello.querySelector( '#lidia-file' );
	var nome      = pannello.querySelector( '#lidia-documento-nome' );
	var vuoto     = pannello.querySelector( '#lidia-documento-vuoto' );
	var avviso    = pannello.querySelector( '#lidia-documento-avviso' );
	var scegli    = pannello.querySelector( '#lidia-documento-scegli' );
	var togli     = pannello.querySelector( '#lidia-documento-togli' );
	var riservati = '/' + ( pannello.getAttribute( 'data-riservati' ) || 'riservati' ) + '/';
	var libreria  = null;

	function mostra( allegato ) {
		if ( ! allegato ) {
			campo.value = '';
			nome.hidden = true;
			vuoto.hidden = false;
			avviso.hidden = true;
			togli.hidden = true;
			scegli.textContent = testi.nuovo;
			return;
		}

		campo.value = allegato.id;
		nome.querySelector( 'strong' ).textContent = allegato.filename || allegato.title;
		nome.hidden = false;
		vuoto.hidden = true;
		togli.hidden = false;
		scegli.textContent = testi.cambia;

		// Il file è protetto solo se sta nella cartella riservata.
		avviso.hidden = allegato.url.indexOf( riservati ) !== -1;
	}

	scegli.addEventListener( 'click', function () {
		if ( ! libreria ) {
			libreria = wp.media( {
				title: testi.titolo,
				multiple: false,
				library: { type: 'application/pdf' },
				button: { text: testi.scegli }
			} );

			libreria.on( 'select', function () {
				var scelto = libreria.state().get( 'selection' ).first().toJSON();

				if ( scelto.mime !== 'application/pdf' ) {
					window.alert( testi.nonPdf );
					return;
				}

				mostra( scelto );
			} );
		}

		libreria.open();
	} );

	togli.addEventListener( 'click', function () {
		mostra( null );
	} );
} )( window.wp, window.lidiaDocumento || {} );
