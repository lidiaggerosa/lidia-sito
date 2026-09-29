/**
 * Il modulo, lato browser.
 *
 * Tre compiti:
 *  1. compilare quello che il server non può sapere perché la pagina può venire dalla cache:
 *     i parametri di campagna (UTM, click id), l'indirizzo vero della pagina, una marca fresca;
 *  2. inviare senza ricaricare la pagina e mostrare conferma o errori sul posto;
 *  3. spingere l'evento di conversione su dataLayer.
 *
 * Se questo file non gira, il modulo resta un form HTML che fa POST e funziona lo stesso.
 * Niente librerie.
 */
( function () {
	'use strict';

	var conf = window.lidiaModulo || {};
	var rotta = conf.rotta || '';
	var rottaMarca = conf.marca || '';
	var chiaviCampagna = conf.campagna || [];
	var CHIAVE_MEMORIA = 'lidia_campagna';

	/* ---------------------------------------------------------------------
	 * Parametri di campagna: si leggono dall'indirizzo, si tengono per la sessione
	 * ------------------------------------------------------------------ */

	function leggiParametri() {
		var trovati = {};
		var presenti = false;
		var params;

		try {
			params = new URLSearchParams( window.location.search );
		} catch ( e ) {
			return null;
		}

		chiaviCampagna.forEach( function ( chiave ) {
			var valore = params.get( chiave );

			if ( valore ) {
				trovati[ chiave ] = valore.slice( 0, 200 );
				presenti = true;
			}
		} );

		return presenti ? trovati : null;
	}

	function ricordaParametri( trovati ) {
		try {
			window.sessionStorage.setItem( CHIAVE_MEMORIA, JSON.stringify( trovati ) );
		} catch ( e ) {
			// Storage non disponibile: i parametri valgono solo su questa pagina.
		}
	}

	function parametriRicordati() {
		try {
			var salvati = window.sessionStorage.getItem( CHIAVE_MEMORIA );

			return salvati ? JSON.parse( salvati ) : null;
		} catch ( e ) {
			return null;
		}
	}

	function parametriCampagna() {
		var dallUrl = leggiParametri();

		if ( dallUrl ) {
			ricordaParametri( dallUrl );

			return dallUrl;
		}

		return parametriRicordati() || {};
	}

	function compilaCampagna( form, parametri ) {
		Object.keys( parametri ).forEach( function ( chiave ) {
			var campo = form.querySelector( '[data-lidia-campagna="' + chiave + '"]' );

			if ( campo && ! campo.value ) {
				campo.value = parametri[ chiave ];
			}
		} );
	}

	function compilaOrigine( form ) {
		var campo = form.querySelector( '[data-lidia="origine"]' );

		if ( ! campo ) {
			return;
		}

		try {
			var url = new URL( window.location.href );

			url.searchParams.delete( 'lidia' );
			url.hash = '';
			campo.value = url.toString();
		} catch ( e ) {
			// Si tiene il valore stampato dal server.
		}
	}

	/* ---------------------------------------------------------------------
	 * Marca fresca: chiesta al server al primo tocco, così il limite dei 3 secondi vale davvero
	 * ------------------------------------------------------------------ */

	function rinnovaMarca( form ) {
		var campo = form.querySelector( '[data-lidia="marca"]' );

		if ( ! campo || ! rottaMarca || ! window.fetch || form.getAttribute( 'data-marca-fresca' ) ) {
			return;
		}

		form.setAttribute( 'data-marca-fresca', '1' );

		fetch( rottaMarca, { credentials: 'same-origin', cache: 'no-store' } )
			.then( function ( risposta ) {
				return risposta.json();
			} )
			.then( function ( dati ) {
				if ( dati && dati.marca ) {
					campo.value = dati.marca;
				}
			} )
			.catch( function () {
				// Resta la marca stampata dal server, che vale comunque.
			} );
	}

	/* ---------------------------------------------------------------------
	 * Errori e conferma
	 * ------------------------------------------------------------------ */

	function pulisci( form ) {
		var vecchi = form.querySelectorAll( '.lidia-campo-errore, .lidia-modulo-allarme' );

		for ( var i = 0; i < vecchi.length; i++ ) {
			vecchi[ i ].parentNode.removeChild( vecchi[ i ] );
		}

		var segnati = form.querySelectorAll( '.lidia-campo--errore' );

		for ( var j = 0; j < segnati.length; j++ ) {
			segnati[ j ].classList.remove( 'lidia-campo--errore' );
		}
	}

	function mostraErrori( form, errori ) {
		var primo = null;

		if ( errori.generale ) {
			var allarme = document.createElement( 'p' );

			allarme.className = 'lidia-modulo-allarme';
			allarme.setAttribute( 'role', 'alert' );
			allarme.textContent = errori.generale;
			form.insertBefore( allarme, form.firstChild );
		}

		Object.keys( errori ).forEach( function ( campo ) {
			if ( 'generale' === campo ) {
				return;
			}

			var input = form.querySelector( '[name="' + campo + '"]' );

			if ( ! input ) {
				return;
			}

			var riga = input.closest( '.lidia-campo, .lidia-consenso' );

			if ( ! riga ) {
				return;
			}

			riga.classList.add( 'lidia-campo--errore' );

			var avviso = document.createElement( 'strong' );

			avviso.className = 'lidia-campo-errore';
			avviso.id = ( input.id || campo ) + '-errore';
			avviso.textContent = errori[ campo ];
			riga.appendChild( avviso );
			input.setAttribute( 'aria-describedby', avviso.id );

			if ( ! primo ) {
				primo = input;
			}
		} );

		if ( primo ) {
			primo.focus();
		} else if ( errori.generale ) {
			var tasto = bottone( form );

			if ( tasto ) {
				tasto.focus();
			}
		}
	}

	function traccia( esito ) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( {
			event: 'lidia_lead',
			tipo: esito.tipo || '',
			intento: esito.intento || '',
			documento: esito.documento || '',
			posizione: esito.posizione || ''
		} );
	}

	function sostituisci( form, html, esito ) {
		var contenitore = document.createElement( 'div' );

		contenitore.innerHTML = html;

		var conferma = contenitore.firstChild;

		form.parentNode.replaceChild( conferma, form );
		traccia( esito || {} );

		if ( conferma.focus ) {
			conferma.focus();
		}

		var scarica = conferma.querySelector( 'a[download]' );

		if ( scarica && scarica.getAttribute( 'href' ) && '#' !== scarica.getAttribute( 'href' ) ) {
			scarica.click();
		}
	}

	function bottone( form ) {
		return form.querySelector( 'button[type="submit"]' );
	}

	function etichettaIntento( form ) {
		var tasto = bottone( form );

		if ( ! tasto || ! tasto.getAttribute( 'data-commerciale' ) ) {
			return;
		}

		var scelto = form.querySelector( 'input[name="intento"]:checked' );
		var valore = scelto ? scelto.value : 'prova';

		tasto.textContent = 'commerciale' === valore
			? tasto.getAttribute( 'data-commerciale' )
			: tasto.getAttribute( 'data-prova' );
	}

	/* ---------------------------------------------------------------------
	 * Invio
	 * ------------------------------------------------------------------ */

	function collega( form, parametri ) {
		compilaCampagna( form, parametri );
		compilaOrigine( form );

		// La marca si rinnova al primo contatto con il modulo, non al caricamento:
		// così il conteggio dei 3 secondi parte quando una persona comincia a scrivere.
		var rinnova = function () {
			rinnovaMarca( form );
		};

		form.addEventListener( 'focusin', rinnova, { once: true } );
		form.addEventListener( 'pointerdown', rinnova, { once: true } );

		var radio = form.querySelectorAll( 'input[name="intento"]' );

		for ( var i = 0; i < radio.length; i++ ) {
			radio[ i ].addEventListener( 'change', function () {
				etichettaIntento( form );
			} );
		}

		form.addEventListener( 'submit', function ( evento ) {
			if ( ! rotta || ! window.fetch || ! window.FormData ) {
				return;
			}

			evento.preventDefault();
			pulisci( form );

			var tasto = bottone( form );
			var prima = tasto ? tasto.textContent : '';

			if ( tasto ) {
				tasto.disabled = true;
				tasto.textContent = tasto.getAttribute( 'data-invio' ) || prima;
			}

			var ripristina = function () {
				if ( tasto ) {
					tasto.disabled = false;
					tasto.textContent = prima;
				}
			};

			fetch( rotta, {
				method: 'POST',
				body: new FormData( form ),
				credentials: 'same-origin'
			} )
				.then( function ( risposta ) {
					return risposta.json();
				} )
				.then( function ( dati ) {
					if ( dati && dati.ok ) {
						sostituisci( form, dati.html, dati.esito );

						return;
					}

					ripristina();
					mostraErrori( form, ( dati && dati.errori ) || {} );
				} )
				.catch( function () {
					// La rete o il server non hanno risposto in modo leggibile. Non si reinvia da
					// soli: la richiesta potrebbe essere già arrivata, e un secondo invio farebbe
					// un lead doppio. Si lascia decidere alla persona.
					ripristina();
					mostraErrori( form, {
						generale: /^en/i.test( document.documentElement.lang || '' )
							? 'The connection dropped. If you don’t receive confirmation within a few seconds, try once more or write to lidia@lidiatech.ai.'
							: 'La connessione si è interrotta. Se non ricevete conferma entro qualche secondo, riprovate una volta sola oppure scriveteci a lidia@lidiatech.ai.'
					} );
				} );
		} );
	}

	function avvia() {
		// Si legge sempre, anche su una pagina senza modulo: chi arriva da una campagna sulla
		// home e compila su /prova-gratuita/ deve arrivare in Delera con la campagna giusta.
		var parametri = parametriCampagna();
		var moduli = document.querySelectorAll( 'form.lidia-modulo' );

		if ( ! moduli.length ) {
			return;
		}

		for ( var i = 0; i < moduli.length; i++ ) {
			collega( moduli[ i ], parametri );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', avvia );
	} else {
		avvia();
	}
} )();
