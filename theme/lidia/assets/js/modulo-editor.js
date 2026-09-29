/**
 * Blocco «Modulo Lidia» — lato editor.
 *
 * Nell'editor non si disegna il modulo: si mostra un segnaposto con il tipo scelto, così la
 * pagina resta leggera e nessuno compila il form per sbaglio mentre scrive. Il markup vero lo
 * produce PHP (inc/forms.php).
 *
 * Niente build: JavaScript semplice, come tutto il resto del tema.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	var tipi = {
		prova: __( 'Prova gratuita / contatto', 'lidia' ),
		whitepaper: __( 'Download whitepaper', 'lidia' )
	};

	var intenti = [
		{ label: __( 'Prova gratuita', 'lidia' ), value: 'prova' },
		{ label: __( 'Team commerciale (offerta Enterprise)', 'lidia' ), value: 'commerciale' }
	];

	var posizioni = [
		{ label: __( 'Non indicata', 'lidia' ), value: '' },
		{ label: __( 'In cima alla pagina', 'lidia' ), value: 'hero' },
		{ label: __( 'In fondo alla pagina', 'lidia' ), value: 'footer' },
		{ label: __( 'Nel corpo della pagina', 'lidia' ), value: 'inline' }
	];

	blocks.registerBlockType( 'lidia/modulo', {
		edit: function ( props ) {
			var tipo = props.attributes.tipo || 'prova';
			var posizione = props.attributes.posizione || '';
			var intento = props.attributes.intento || 'prova';
			var proprieta = blockEditor.useBlockProps( {
				className: 'lidia-modulo-blocco lidia-modulo-blocco--editor'
			} );

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Modulo', 'lidia' ) },
						el( components.SelectControl, {
							label: __( 'Tipo di modulo', 'lidia' ),
							help: __( 'Il modulo di download va solo sulle pagine dei whitepaper: prende il documento dalla pagina in cui si trova.', 'lidia' ),
							value: tipo,
							options: [
								{ label: tipi.prova, value: 'prova' },
								{ label: tipi.whitepaper, value: 'whitepaper' }
							],
							onChange: function ( valore ) {
								props.setAttributes( { tipo: valore } );
							}
						} ),
						'prova' === tipo && el( components.SelectControl, {
							label: __( 'A chi arriva', 'lidia' ),
							help: __( 'Prova gratuita: home e pagina della prova. Team commerciale: pagina contatti, per chi vuole personalizzare Lidia e fissare un incontro.', 'lidia' ),
							value: intento,
							options: intenti,
							onChange: function ( valore ) {
								props.setAttributes( { intento: valore } );
							}
						} ),
						el( components.SelectControl, {
							label: __( 'Posizione nella pagina', 'lidia' ),
							help: __( 'Serve solo alle statistiche: distingue il modulo in cima da quello in fondo alla stessa pagina.', 'lidia' ),
							value: posizione,
							options: posizioni,
							onChange: function ( valore ) {
								props.setAttributes( { posizione: valore } );
							}
						} )
					)
				),
				el(
					'div',
					proprieta,
					el(
						components.Placeholder,
						{
							icon: 'feedback',
							label: __( 'Modulo Lidia', 'lidia' ),
							instructions: __( 'Il modulo si vede sul sito pubblicato, non qui. I campi sono fissi: si cambiano nel tema, non in pagina.', 'lidia' )
						},
						el( 'code', null, ( tipi[ tipo ] || tipo ) + ( 'prova' === tipo && 'commerciale' === intento ? ' · commerciale' : '' ) + ( posizione ? ' · ' + posizione : '' ) )
					)
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
