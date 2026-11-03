/**
 * Questionari di feedback (29/10/2026) — sezioni progressive, campi obbligatori, invio.
 * Markup: inc/feedback.php (blocco lidia/feedback). Nessuna dipendenza.
 */
( function () {
	'use strict';

	var radice = document.querySelector( '[data-lidia-feedback]' );
	if ( ! radice ) {
		return;
	}

	var modulo = radice.querySelector( 'form' );
	var sezioni = Array.prototype.slice.call( modulo.querySelectorAll( '.lidia-fb-sezione' ) );
	var inizio = Date.now();
	var corrente = 0;
	var riduci = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function scelto( nome ) {
		var campo = modulo.querySelector( '[name="' + nome + '"]:checked' );
		return campo ? campo.value : null;
	}

	function scelti( nome ) {
		return Array.prototype.slice.call( modulo.querySelectorAll( '[name="' + nome + '"]:checked' ) ).map( function ( c ) {
			return c.value;
		} );
	}

	function testo( nome ) {
		var campo = modulo.elements[ nome ];
		return campo && campo.value ? campo.value.trim() : '';
	}

	function voto( nome ) {
		var v = scelto( nome );
		return 'na' === v ? null : v;
	}

	function mancanti( sezione ) {
		return ( sezione.getAttribute( 'data-obbligatorie' ) || '' ).split( ',' ).filter( Boolean ).some( function ( nome ) {
			return null === scelto( nome );
		} );
	}

	function controlla( sezione ) {
		var errore = sezione.querySelector( '.lidia-fb-errore' );
		if ( mancanti( sezione ) ) {
			errore.textContent = 'Rispondi alle domande a scelta per proseguire.';
			return false;
		}
		errore.textContent = '';
		return true;
	}

	function avanzamento() {
		radice.querySelector( '[data-numero]' ).textContent = ( corrente + 1 ) + '/' + sezioni.length;
		radice.querySelector( '[data-titolo]' ).textContent = sezioni[ corrente ].querySelector( 'h2' ).textContent;
		radice.querySelector( '[data-barra]' ).style.width = ( ( corrente + 1 ) / sezioni.length * 100 ) + '%';
	}

	function apri( i, primaVolta ) {
		corrente = i;
		sezioni[ i ].hidden = false;
		sezioni.forEach( function ( s, j ) {
			s.classList.toggle( 'is-passata', j < i );
		} );
		avanzamento();
		if ( primaVolta ) {
			return;
		}
		sezioni[ i ].scrollIntoView( { behavior: riduci ? 'auto' : 'smooth', block: 'start' } );
		sezioni[ i ].querySelector( 'h2' ).focus( { preventScroll: true } );
	}

	sezioni.forEach( function ( sezione, i ) {
		var bottone = sezione.querySelector( '[data-avanti]' );
		if ( ! bottone ) {
			return;
		}
		bottone.addEventListener( 'click', function () {
			if ( ! controlla( sezione ) ) {
				return;
			}
			if ( i + 1 > corrente ) {
				apri( i + 1 );
			} else {
				sezioni[ i + 1 ].scrollIntoView( { block: 'start' } );
			}
		} );
	} );

	// «Non so» esclude le altre funzioni, e viceversa.
	modulo.addEventListener( 'change', function ( e ) {
		var c = e.target;
		if ( 'checkbox' !== c.type || ! c.checked ) {
			return;
		}
		var esclusivo = c.hasAttribute( 'data-esclusivo' );
		modulo.querySelectorAll( '[name="' + c.name + '"]' ).forEach( function ( altro ) {
			if ( altro !== c && ( esclusivo || altro.hasAttribute( 'data-esclusivo' ) ) ) {
				altro.checked = false;
			}
		} );
	} );

	// Domande condizionali.
	function aggiornaCondizioni() {
		radice.querySelectorAll( '[data-mostra-se]' ).forEach( function ( blocco ) {
			var v = scelto( blocco.getAttribute( 'data-mostra-se' ) );
			blocco.hidden = null === v || v === blocco.getAttribute( 'data-tranne' );
		} );
	}
	modulo.addEventListener( 'change', aggiornaCondizioni );

	function corpo() {
		var ambiti = 'No' === scelto( 'agente' ) ? [] : scelti( 'agente_ambiti' );
		var b = {
			frequenza: scelto( 'frequenza' ),
			funzioni: scelti( 'funzioni' ),
			voti: {
				ricerca: voto( 'v_ricerca' ),
				workflow: voto( 'v_workflow' ),
				smart: voto( 'v_smart' ),
				chat: voto( 'v_chat' ),
				word: voto( 'v_word' )
			},
			piu_utile: testo( 'piu_utile' ),
			poco_usata: testo( 'poco_usata' ),
			diversa: scelto( 'diversa' ),
			in_cosa: testo( 'in_cosa' ),
			altri: testo( 'altri' ),
			fiducia: scelto( 'fiducia' ),
			fonti: scelto( 'fonti' ),
			qualita: scelto( 'qualita' ),
			facilita: scelto( 'facilita' ),
			velocita: scelto( 'velocita' ),
			formazione: voto( 'formazione' ),
			helpdesk: voto( 'helpdesk' ),
			manca: testo( 'manca' ),
			cambiare: testo( 'cambiare' ),
			agente: scelto( 'agente' ),
			agente_ambiti: ambiti,
			agente_altro: 'No' === scelto( 'agente' ) ? '' : testo( 'agente_altro' ),
			nps: scelto( 'nps' ),
			motivo: testo( 'motivo' )
		};

		b.sito_web = testo( 'sito_web' );
		b.marca = testo( 'marca' );
		b.ms = Date.now() - inizio;
		return b;
	}

	modulo.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var ultima = sezioni[ sezioni.length - 1 ];
		for ( var i = 0; i < sezioni.length; i++ ) {
			if ( ! controlla( sezioni[ i ] ) ) {
				sezioni[ i ].scrollIntoView( { block: 'start' } );
				return;
			}
		}

		var bottone = ultima.querySelector( 'button[type="submit"]' );
		var errore = ultima.querySelector( '.lidia-fb-errore' );
		bottone.disabled = true;
		errore.textContent = '';

		fetch( modulo.getAttribute( 'action' ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			credentials: 'same-origin',
			body: JSON.stringify( corpo() )
		} ).then( function ( r ) {
			if ( ! r.ok ) {
				throw new Error( r.status );
			}
			radice.querySelector( '.lidia-fb-intro' ).hidden = true;
			modulo.hidden = true;
			var grazie = radice.querySelector( '.lidia-fb-grazie' );
			grazie.hidden = false;
			grazie.scrollIntoView( { block: 'start' } );
			grazie.focus( { preventScroll: true } );
		} ).catch( function () {
			bottone.disabled = false;
			errore.textContent = 'Invio non riuscito. Riprova tra poco.';
		} );
	} );

	apri( 0, true );
}() );
