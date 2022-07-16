/*
 * JavaScript for WikibaseRDF
 */

$( function () {
	'use strict';

	var main = $( '.wikibase-entitytermsview' );
	var mappingsContainer = $( '<div>' );
	mappingsContainer.css( 'border', '1px solid red' );
	main.append( mappingsContainer );

	var stuff = $( '<div>' );
	stuff.text( 'abc' );
	mappingsContainer.append( stuff );

	var toggler = $( '<a>' ).toggler( {
		$subject: stuff,
		visible: true
	} );
	toggler.find( '.ui-toggler-label' ).text( 'mapping to other ontologies' );
	mappingsContainer.prepend(toggler);
} );
