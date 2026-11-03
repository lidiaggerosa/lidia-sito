/**
 * Il modulo, lato browser.
 *
 * Tre compiti:
 *  1. compilare quello che il server non può sapere perché la pagina può venire dalla cache:
 *     l'indirizzo vero della pagina, una marca fresca, e la provenienza del visitatore
 *     (attribuzione: primo, intermedi, ultimo contatto);
 *  2. inviare senza ricaricare la pagina e mostrare conferma o errori sul posto;
 *  3. spingere l'evento di conversione su dataLayer.
 *
 * Se questo file non gira, il modulo resta un form HTML che fa POST e funziona lo stesso
 * (senza attribuzione).
 * Niente librerie.
 */
( function () {
	'use strict';

	var conf = window.lidiaModulo || {};
	var rotta = conf.rotta || '';
	var rottaMarca = conf.marca || '';
	var chiaviCampagna = conf.campagna || [];

	/* ---------------------------------------------------------------------
	 * Attribuzione
	 *
	 * Un «contatto» è un arrivo sul sito da una fonte: URL con UTM o click id, oppure un sito
	 * esterno (motore di ricerca → organic, social → social, altro → referral). Gli arrivi diretti
	 * e la navigazione interna non sono contatti; la stessa fonte ripetuta nella stessa visita
	 * nemmeno.
	 *
	 * Senza consenso: il contatto della visita resta in sessionStorage (come prima del 20/10) e
	 * arriva a Delera come ultimo contatto.
	 * Con il consenso marketing di Complianz: la storia (primo, ultimi 5 intermedi, ultimo,
	 * totale) sta nel cookie first-party `lidia_attribuzione`, 90 giorni. Negato o revocato →
	 * il cookie si cancella. Il server lo legge all'invio (inc/forms.php).
	 * ------------------------------------------------------------------ */

	var COOKIE_ATTRIBUZIONE = 'lidia_attribuzione';
	var DURATA_ATTRIBUZIONE = 90 * 24 * 60 * 60;
	var MAX_INTERMEDI = 5;
	var MAX_COOKIE = 3800;
	var CHIAVE_SESSIONE = 'lidia_contatto';
	var CHIAVE_SCRITTO = 'lidia_contatto_scritto';

	var MOTORI = {
		google: /(^|\.)google\./,
		bing: /(^|\.)bing\.com$/,
		duckduckgo: /(^|\.)duckduckgo\.com$/,
		yahoo: /(^|\.)yahoo\./,
		ecosia: /(^|\.)ecosia\.org$/,
		qwant: /(^|\.)qwant\.com$/,
		brave: /^search\.brave\.com$/,
		startpage: /(^|\.)startpage\.com$/,
		yandex: /(^|\.)yandex\./,
		baidu: /(^|\.)baidu\.com$/
	};

	var SOCIAL = {
		linkedin: /(^|\.)(linkedin\.com|lnkd\.in)$/,
		facebook: /(^|\.)(facebook\.com|fb\.com|fb\.me)$/,
		instagram: /(^|\.)instagram\.com$/,
		x: /(^|\.)(x\.com|twitter\.com|t\.co)$/,
		youtube: /(^|\.)(youtube\.com|youtu\.be)$/,
		threads: /(^|\.)threads\.net$/
	};

	function taglia( valore, quanto ) {
		return String( valore || '' ).slice( 0, quanto || 100 );
	}

	function oggi() {
		var d = new Date();
		var due = function ( n ) {
			return n < 10 ? '0' + n : String( n );
		};

		return d.getFullYear() + '-' + due( d.getMonth() + 1 ) + '-' + due( d.getDate() );
	}

	function nostroHost( host ) {
		return host === window.location.hostname || /(^|\.)lidiatech\.ai$/.test( host );
	}

	function cerca( elenco, host ) {
		var nomi = Object.keys( elenco );

		for ( var i = 0; i < nomi.length; i++ ) {
			if ( elenco[ nomi[ i ] ].test( host ) ) {
				return nomi[ i ];
			}
		}

		return '';
	}

	/** Il contatto di questo arrivo, o null se è diretto o interno. */
	function contattoDiQuestoArrivo() {
		var params;
		var click = {};
		var haClick = false;

		try {
			params = new URLSearchParams( window.location.search );
		} catch ( e ) {
			return null;
		}

		chiaviCampagna.forEach( function ( chiave ) {
			if ( 0 !== chiave.indexOf( 'utm_' ) && params.get( chiave ) ) {
				click[ chiave ] = taglia( params.get( chiave ), 200 );
				haClick = true;
			}
		} );

		var contatto = {
			utm_source: taglia( params.get( 'utm_source' ) ),
			utm_medium: taglia( params.get( 'utm_medium' ) ),
			utm_campaign: taglia( params.get( 'utm_campaign' ) ),
			utm_content: taglia( params.get( 'utm_content' ) ),
			utm_term: taglia( params.get( 'utm_term' ) ),
			pagina: taglia( window.location.pathname ),
			data: oggi()
		};

		if ( contatto.utm_source || contatto.utm_medium || contatto.utm_campaign || haClick ) {
			if ( ! contatto.utm_source ) {
				// Solo il click id: la fonte si ricava da quale click id è.
				if ( click.gclid || click.wbraid || click.gbraid ) {
					contatto.utm_source = 'google';
					contatto.utm_medium = contatto.utm_medium || 'cpc';
				} else if ( click.msclkid ) {
					contatto.utm_source = 'bing';
					contatto.utm_medium = contatto.utm_medium || 'cpc';
				} else if ( click.li_fat_id ) {
					contatto.utm_source = 'linkedin';
					contatto.utm_medium = contatto.utm_medium || 'paid';
				} else if ( click.fbclid ) {
					contatto.utm_source = 'facebook';
					contatto.utm_medium = contatto.utm_medium || 'social';
				}
			}

			contatto.click = click;

			return contatto;
		}

		var host = '';

		try {
			host = document.referrer ? new URL( document.referrer ).hostname.toLowerCase() : '';
		} catch ( e ) {
			host = '';
		}

		if ( ! host || nostroHost( host ) ) {
			return null;
		}

		var motore = cerca( MOTORI, host );
		var social = motore ? '' : cerca( SOCIAL, host );

		contatto.utm_source = motore || social || taglia( host.replace( /^www\./, '' ) );
		contatto.utm_medium = motore ? 'organic' : ( social ? 'social' : 'referral' );
		contatto.click = {};

		return contatto;
	}

	function firma( contatto ) {
		return contatto ? [ contatto.utm_source, contatto.utm_medium, contatto.utm_campaign, contatto.utm_content, contatto.utm_term ].join( '|' ) : '';
	}

	function sessioneLeggi( chiave ) {
		try {
			return window.sessionStorage.getItem( chiave );
		} catch ( e ) {
			return null;
		}
	}

	function sessioneScrivi( chiave, valore ) {
		try {
			window.sessionStorage.setItem( chiave, valore );
		} catch ( e ) {
			// Storage non disponibile: il contatto vale solo su questa pagina.
		}
	}

	var contattoSessione = null;

	function contattoDellaVisita() {
		if ( contattoSessione ) {
			return contattoSessione;
		}

		try {
			var salvato = sessioneLeggi( CHIAVE_SESSIONE );

			contattoSessione = salvato ? JSON.parse( salvato ) : null;
		} catch ( e ) {
			contattoSessione = null;
		}

		return contattoSessione;
	}

	function leggiCookie( nome ) {
		var coppie = document.cookie ? document.cookie.split( '; ' ) : [];

		for ( var i = 0; i < coppie.length; i++ ) {
			var uguale = coppie[ i ].indexOf( '=' );

			if ( coppie[ i ].slice( 0, uguale ) === nome ) {
				return coppie[ i ].slice( uguale + 1 );
			}
		}

		return null;
	}

	/** Consenso marketing di Complianz: vero, falso, o null se non c'è ancora una scelta. */
	function consensoMarketing() {
		if ( 'function' === typeof window.cmplz_has_consent ) {
			try {
				if ( window.cmplz_has_consent( 'marketing' ) ) {
					return true;
				}
			} catch ( e ) {
				// Si passa al cookie.
			}
		}

		var stato = leggiCookie( 'cmplz_marketing' );

		if ( 'allow' === stato ) {
			return true;
		}

		return 'deny' === stato ? false : null;
	}

	function storiaLeggi() {
		var grezzo = leggiCookie( COOKIE_ATTRIBUZIONE );

		if ( ! grezzo ) {
			return null;
		}

		try {
			var storia = JSON.parse( decodeURIComponent( grezzo ) );

			return storia && storia.primo ? storia : null;
		} catch ( e ) {
			return null;
		}
	}

	function storiaScrivi( storia ) {
		var sicuro = 'https:' === window.location.protocol ? '; Secure' : '';
		var valore = encodeURIComponent( JSON.stringify( storia ) );

		// Il cookie deve stare sotto i 4 KB: si sacrificano gli intermedi più vecchi.
		while ( valore.length > MAX_COOKIE && storia.intermedi.length ) {
			storia.intermedi.shift();
			valore = encodeURIComponent( JSON.stringify( storia ) );
		}

		document.cookie = COOKIE_ATTRIBUZIONE + '=' + valore + '; Max-Age=' + DURATA_ATTRIBUZIONE + '; Path=/; SameSite=Lax' + sicuro;
	}

	function storiaCancella() {
		if ( null !== leggiCookie( COOKIE_ATTRIBUZIONE ) ) {
			document.cookie = COOKIE_ATTRIBUZIONE + '=; Max-Age=0; Path=/; SameSite=Lax';
		}
	}

	/** Nei contatti non ultimi bastano le quattro voci di campagna: il cookie resta piccolo. */
	function ridotto( contatto ) {
		return {
			utm_source: contatto.utm_source,
			utm_medium: contatto.utm_medium,
			utm_campaign: contatto.utm_campaign,
			utm_content: contatto.utm_content
		};
	}

	function senzaClick( contatto ) {
		return {
			utm_source: contatto.utm_source,
			utm_medium: contatto.utm_medium,
			utm_campaign: contatto.utm_campaign,
			utm_content: contatto.utm_content,
			utm_term: contatto.utm_term,
			pagina: contatto.pagina,
			data: contatto.data
		};
	}

	function storiaAggiungi( contatto ) {
		var storia = storiaLeggi();

		if ( ! storia ) {
			storia = { primo: senzaClick( contatto ), intermedi: [], ultimo: contatto, totali: 1 };
		} else {
			if ( storia.totali > 1 && storia.ultimo ) {
				storia.intermedi.push( ridotto( storia.ultimo ) );
				storia.intermedi = storia.intermedi.slice( -MAX_INTERMEDI );
			}

			storia.ultimo = contatto;
			storia.totali = ( parseInt( storia.totali, 10 ) || 1 ) + 1;
		}

		storiaScrivi( storia );
		sessioneScrivi( CHIAVE_SCRITTO, firma( contatto ) );
	}

	/** Con il consenso, il contatto della visita entra nella storia (una volta sola). */
	function applicaConsenso() {
		var consenso = consensoMarketing();

		if ( true === consenso ) {
			var contatto = contattoDellaVisita();

			if ( contatto && sessioneLeggi( CHIAVE_SCRITTO ) !== firma( contatto ) ) {
				storiaAggiungi( contatto );
			}

			return;
		}

		if ( false === consenso ) {
			storiaCancella();
		}
	}

	function avviaAttribuzione() {
		var nuovo = contattoDiQuestoArrivo();
		var visita = contattoDellaVisita();

		if ( nuovo && firma( nuovo ) !== firma( visita ) ) {
			contattoSessione = nuovo;
			sessioneScrivi( CHIAVE_SESSIONE, JSON.stringify( nuovo ) );
		}

		applicaConsenso();

		// Il consenso può arrivare (o essere revocato) dal banner mentre si è sulla pagina.
		document.addEventListener( 'cmplz_fire_categories', applicaConsenso );
		document.addEventListener( 'cmplz_status_change', applicaConsenso );
		document.addEventListener( 'cmplz_revoke', storiaCancella );
	}

	/** Il contatto della visita viaggia con il modulo: è l'ultimo contatto anche senza consenso. */
	function compilaContatto( form ) {
		var campo = form.querySelector( '[data-lidia="contatto"]' );
		var contatto = contattoDellaVisita();

		if ( campo ) {
			campo.value = contatto ? JSON.stringify( contatto ) : '';
		}
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

	function collega( form ) {
		compilaOrigine( form );
		compilaContatto( form );

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
			compilaContatto( form );

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
		// Gira sempre, anche su una pagina senza modulo: chi arriva da una campagna sulla
		// home e compila su /prova-gratuita/ deve arrivare in Delera con la campagna giusta.
		avviaAttribuzione();

		var moduli = document.querySelectorAll( 'form.lidia-modulo' );

		if ( ! moduli.length ) {
			return;
		}

		for ( var i = 0; i < moduli.length; i++ ) {
			collega( moduli[ i ] );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', avvia );
	} else {
		avvia();
	}
} )();
