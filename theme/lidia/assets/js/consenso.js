/**
 * «Preferenze cookie» nel footer riapre il banner di Complianz.
 * Complianz free apre il banner solo dal suo bottone «Gestisci consenso»:
 * il link del footer lo inoltra a quel bottone.
 */
( function () {
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a[href="#preferenze-cookie"], .cmplz-show-banner' );
		if ( ! link ) {
			return;
		}
		var bottone = document.querySelector( '.cmplz-manage-consent' );
		if ( ! bottone ) {
			return;
		}
		e.preventDefault();
		bottone.click();
	} );
} )();
