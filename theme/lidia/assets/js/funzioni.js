/**
 * Fila delle funzioni (home, sezione 4): frecce sotto la fila e trascinamento al
 * posto della barra di scorrimento. Progressivo: senza questo script la fila scorre con la
 * barra; con lo script la barra sparisce (classe `lidia-fila-js`).
 *
 * Nessuna dipendenza. ~2 KB.
 */
( function () {
	'use strict';

	var FRECCIA_SX = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.5 4.5 7 10l5.5 5.5"/></svg>';
	var FRECCIA_DX = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7.5 4.5 5.5 5.5-5.5 5.5"/></svg>';

	function bottone( direzione, etichetta ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'lidia-freccia-scorri lidia-freccia-' + direzione;
		b.setAttribute( 'aria-label', etichetta );
		b.innerHTML = 'sx' === direzione ? FRECCIA_SX : FRECCIA_DX;
		return b;
	}

	function passo( fila ) {
		var prima = fila.firstElementChild;
		if ( ! prima ) {
			return fila.clientWidth * 0.8;
		}
		var stile = window.getComputedStyle( fila );
		var gap = parseFloat( stile.columnGap || stile.gap ) || 0;
		return prima.getBoundingClientRect().width + gap;
	}

	function attiva( sezione ) {
		var fila = sezione.querySelector( '.lidia-fila' );

		if ( ! fila || fila.classList.contains( 'lidia-fila-js' ) ) {
			return;
		}

		fila.classList.add( 'lidia-fila-js' );

		var en = /^en/i.test( document.documentElement.lang || '' );
		var sx = bottone( 'sx', en ? 'Previous features' : 'Funzioni precedenti' );
		var dx = bottone( 'dx', en ? 'Next features' : 'Funzioni successive' );
		var frecce = document.createElement( 'div' );
		frecce.className = 'lidia-frecce';
		frecce.appendChild( sx );
		frecce.appendChild( dx );
		// Sotto la fila, centrate: la testa resta solo titolo.
		fila.insertAdjacentElement( 'afterend', frecce );

		function aggiorna() {
			var max = fila.scrollWidth - fila.clientWidth - 1;
			sx.disabled = fila.scrollLeft <= 1;
			dx.disabled = fila.scrollLeft >= max;
			frecce.hidden = max <= 1;
		}

		sx.addEventListener( 'click', function () {
			fila.scrollBy( { left: -passo( fila ), behavior: 'smooth' } );
		} );

		dx.addEventListener( 'click', function () {
			fila.scrollBy( { left: passo( fila ), behavior: 'smooth' } );
		} );

		fila.addEventListener( 'scroll', aggiorna, { passive: true } );
		window.addEventListener( 'resize', aggiorna );

		// Trascinamento con il mouse. Sul touch lo fa già il browser.
		var trascino = false;
		var partenzaX = 0;
		var partenzaScroll = 0;
		var mosso = false;

		fila.addEventListener( 'pointerdown', function ( e ) {
			if ( 'mouse' !== e.pointerType || 0 !== e.button ) {
				return;
			}
			trascino = true;
			mosso = false;
			partenzaX = e.clientX;
			partenzaScroll = fila.scrollLeft;
			fila.setPointerCapture( e.pointerId );
		} );

		fila.addEventListener( 'pointermove', function ( e ) {
			if ( ! trascino ) {
				return;
			}
			var dx = e.clientX - partenzaX;
			if ( ! mosso && Math.abs( dx ) < 4 ) {
				return;
			}
			if ( ! mosso ) {
				mosso = true;
				fila.classList.add( 'lidia-fila-trascina' );
			}
			fila.scrollLeft = partenzaScroll - dx;
		} );

		function rilascia( e ) {
			if ( ! trascino ) {
				return;
			}
			trascino = false;
			if ( fila.hasPointerCapture && fila.hasPointerCapture( e.pointerId ) ) {
				fila.releasePointerCapture( e.pointerId );
			}
			if ( mosso ) {
				// Il rilascio riattiva lo snap: la scheda più vicina si allinea da sola.
				fila.classList.remove( 'lidia-fila-trascina' );
				var p = passo( fila );
				fila.scrollTo( { left: Math.round( fila.scrollLeft / p ) * p, behavior: 'smooth' } );
			}
		}

		fila.addEventListener( 'pointerup', rilascia );
		fila.addEventListener( 'pointercancel', rilascia );
		fila.addEventListener( 'click', function ( e ) {
			if ( mosso ) {
				e.preventDefault();
			}
		}, true );

		aggiorna();
	}

	function avvia() {
		var sezioni = document.querySelectorAll( '.lidia-funzioni' );
		for ( var i = 0; i < sezioni.length; i++ ) {
			attiva( sezioni[ i ] );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', avvia );
	} else {
		avvia();
	}
} )();
