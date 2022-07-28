/*
 * JavaScript for WikibaseRDF
 */

$( function () {
	'use strict';

	// Move Mappings before Statements section.
	$( 'h2.wikibase-statements' ).before( $( '#wikibase-rdf' ) );

	mw.loader.using(
		'wikibase.view.ControllerViewFactory',
		function () {
			// TODO: toggler state needs to be remembered.
			var toggler = $( '#wikibase-rdf-toggler' ).toggler( {
				$subject: $( '#wikibase-rdf-mappings' ),
				visible: false
			} );
			toggler.find( '.ui-toggler-label' ).text( mw.msg( 'wikibase-rdf-mappings-toggler' ) );
		}
	);

} );
