/**
 * Blocco «Libreria delle sezioni» — lato editor.
 *
 * Nell'editor si mostra solo un segnaposto: il contenuto lo genera PHP a ogni
 * richiesta (inc/libreria.php), leggendo i pattern registrati dal tema.
 *
 * Niente build: JavaScript semplice, come tutto il resto del tema.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'lidia/libreria', {
		edit: function () {
			var proprieta = blockEditor.useBlockProps( {
				className: 'lidia-libreria-blocco lidia-libreria-blocco--editor'
			} );

			return el(
				'div',
				proprieta,
				el(
					components.Placeholder,
					{
						icon: 'layout',
						label: __( 'Libreria delle sezioni', 'lidia' ),
						instructions: __( 'Tutte le sezioni del tema, una sotto l’altra, con la loro scheda. Si vede sul sito, non qui: il contenuto segue il tema da solo.', 'lidia' )
					}
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
