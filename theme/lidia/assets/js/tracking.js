/**
 * Eventi del sito per il dataLayer — tracking/datalayer-spec.md §3.
 *
 * Un solo ascoltatore per tipo, su `document`: nessun handler per pagina, nessun attributo da
 * mettere a mano sulle CTA. Il sito spinge gli eventi sempre; cosa arriva a GA4, Meta, LinkedIn e
 * Google Ads lo decidono GTM e il consenso. Nessun dato personale: mai email, telefono o nome
 * (il `mailto:` e il `tel:` si contano, non si leggono).
 *
 * `lidia_lead` non sta qui: lo spinge assets/js/modulo.js alla conferma dell'invio.
 *
 * Nessuna dipendenza. ~2 KB.
 */
( function () {
	'use strict';

	window.dataLayer = window.dataLayer || [];

	function spingi( evento, dati ) {
		var voce = { event: evento };
		for ( var chiave in dati ) {
			if ( Object.prototype.hasOwnProperty.call( dati, chiave ) ) {
				voce[ chiave ] = dati[ chiave ];
			}
		}
		window.dataLayer.push( voce );
	}

	/** Il contesto di pagina spinto dal mu-plugin, se c'è (in produzione). */
	function contesto() {
		for ( var i = 0; i < window.dataLayer.length; i++ ) {
			var v = window.dataLayer[ i ];
			if ( v && 'lidia_page_context' === v.event ) {
				return v;
			}
		}
		return {};
	}

	function testo( el ) {
		return ( el.getAttribute( 'data-track-testo' ) || el.textContent || '' ).replace( /\s+/g, ' ' ).trim().slice( 0, 100 );
	}

	/* --- Click: download, contatti, link esterni, CTA -------------------------- */

	document.addEventListener( 'click', function ( e ) {
		var a = e.target && e.target.closest ? e.target.closest( 'a[href]' ) : null;

		if ( ! a ) {
			return;
		}

		var href = a.getAttribute( 'href' ) || '';
		var url;

		try {
			url = new URL( a.href, window.location.href );
		} catch ( err ) {
			return;
		}

		// 1. Il link firmato del whitepaper.
		if ( 'download' === a.getAttribute( 'data-track' ) || url.searchParams.has( 'lidia_dl' ) ) {
			spingi( 'lidia_download', { documento: a.getAttribute( 'data-track-documento' ) || '' } );
			return;
		}

		// 2. Email e telefono: si conta il tipo, non il recapito.
		if ( 0 === href.indexOf( 'mailto:' ) || 0 === href.indexOf( 'tel:' ) ) {
			spingi( 'lidia_contact_click', { contatto_tipo: 0 === href.indexOf( 'mailto:' ) ? 'email' : 'telefono' } );
			return;
		}

		// 3. Link verso un altro dominio (compreso «Log in», che porta all'applicazione).
		if ( /^https?:$/.test( url.protocol ) && url.host !== window.location.host ) {
			spingi( 'lidia_outbound_click', { link_url: url.origin + url.pathname, link_dominio: url.host } );
			return;
		}

		// 4. CTA: i bottoni del tema, o un link marcato a mano con data-track="cta".
		if ( 'cta' === a.getAttribute( 'data-track' ) || a.classList.contains( 'wp-block-button__link' ) ) {
			spingi( 'lidia_cta_click', {
				cta_testo: testo( a ),
				cta_destinazione: url.host === window.location.host ? url.pathname + url.hash : url.href,
				page_type: contesto().page_type || ''
			} );
		}
	} );

	/* --- Modulo: visto e iniziato, una volta per modulo --------------------------- */

	function moduli() {
		return Array.prototype.slice.call( document.querySelectorAll( 'form.lidia-modulo' ) );
	}

	var visti = [];

	if ( 'IntersectionObserver' in window ) {
		var osservatore = new IntersectionObserver( function ( voci ) {
			voci.forEach( function ( voce ) {
				if ( voce.isIntersecting && -1 === visti.indexOf( voce.target ) ) {
					visti.push( voce.target );
					osservatore.unobserve( voce.target );
					spingi( 'lidia_form_view', { tipo: voce.target.getAttribute( 'data-tipo' ) || '' } );
				}
			} );
		}, { threshold: 0.4 } );

		moduli().forEach( function ( f ) {
			osservatore.observe( f );
		} );
	}

	var iniziati = [];

	function inizio( e ) {
		var campo = e.target;
		var form = campo && campo.closest ? campo.closest( 'form.lidia-modulo' ) : null;

		if ( ! form || 'hidden' === campo.type || -1 !== iniziati.indexOf( form ) || campo.closest( '.lidia-esca' ) ) {
			return;
		}

		iniziati.push( form );
		spingi( 'lidia_form_start', { tipo: form.getAttribute( 'data-tipo' ) || '' } );
	}

	document.addEventListener( 'input', inizio );
	document.addEventListener( 'change', inizio );

	/* --- Scroll al 75% su articoli e whitepaper ----------------------------------- */

	var corpo = document.body;

	if ( corpo && ( corpo.classList.contains( 'single-post' ) || corpo.classList.contains( 'single-risorsa' ) ) ) {
		var fatto = false;

		var misura = function () {
			if ( fatto ) {
				return;
			}

			var alto = document.documentElement.scrollHeight - window.innerHeight;

			if ( alto > 0 && window.scrollY / alto >= 0.75 ) {
				fatto = true;
				window.removeEventListener( 'scroll', misura );
				var c = contesto();
				spingi( 'lidia_scroll_deep', { page_type: c.page_type || '', content_title: c.content_title || document.title } );
			}
		};

		window.addEventListener( 'scroll', misura, { passive: true } );
	}
}() );
