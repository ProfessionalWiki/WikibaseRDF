/*
 * JavaScript for WikibaseRDF
 */

$( function () {
	'use strict';

	moveSection();

	mw.loader.using( 'wikibase.view.ControllerViewFactory', addToggler );

	$( '.wikibase-rdf-action-add' ).click( clickAdd );
	let rdfSection = $('#wikibase-rdf');
	rdfSection.on( 'click', '.wikibase-rdf-action-edit', clickEdit );
	rdfSection.on( 'click', '.wikibase-rdf-action-save', clickSave );
	rdfSection.on( 'click', '.wikibase-rdf-action-remove', clickRemove );
	rdfSection.on( 'click', '.wikibase-rdf-action-cancel', clickCancel );

	function moveSection() {
		// Move Mappings before Statements section.
		$('#wikibase-rdf').insertBefore( $( 'h2.wikibase-statements' ) ).show();
	}

	function addToggler() {
		// TODO: toggler state needs to be remembered.
		var toggler = $( '#wikibase-rdf-toggler' ).toggler( {
			$subject: $( '#wikibase-rdf-mappings' ),
			visible: false
		} );
		toggler.find( '.ui-toggler-label' ).text( mw.msg( 'wikibase-rdf-mappings-toggler' ) );
	}

	function findRow( element ) {
		return $( element ).parents( '.wikibase-rdf-row' );
	}

	function clickEdit( event ) {
		event.preventDefault();

		let row = findRow( this );
		row.html( $( '.wikibase-rdf-row-editing-template' ).html() );
		row.addClass( 'wikibase-rdf-row-editing-existing' );
		row.find( '[name="wikibase-rdf-predicate"]' ).val( row.data( 'predicate' ) ).change();
		row.find( '[name="wikibase-rdf-object"]' ).val( row.data( 'object' ) ).change();
	}

	function saveMappings( trigger ) {
		// TODO: get all mappings from form
		let mappings = [];

		findRow( trigger ).addClass( 'wikibase-rdf-row-editing-saving' );

		$( '.wikibase-rdf-rows .wikibase-rdf-row' ).each( function( index, row ) {
			let mapping = { predicate: '', object: '' };

			let $row = $( row );
			let isAdd = $row.hasClass( 'wikibase-rdf-row-editing-add' );
			let isEdit = $row.hasClass( 'wikibase-rdf-row-editing-existing' );
			let isTrigger = $row.hasClass( 'wikibase-rdf-row-editing-saving' );
			let isRemove = $( trigger ).hasClass( 'wikibase-rdf-action-remove' );

			if ( isTrigger && isRemove ) {
				return;
			} else if ( ( isAdd || isEdit ) && isTrigger ) {
				// Rows in edit mode should not be saved, unless it was the triggering row.
				mapping.predicate = $row.find( '[name="wikibase-rdf-predicate"] :selected' ).val();
				mapping.object = $row.find( '[name="wikibase-rdf-object"]' ).val();
				$row.removeClass( 'wikibase-rdf-row-editing-saving' )
			} else {
				mapping.predicate = $row.data( 'predicate' );
				mapping.object = $row.data( 'object' );
			}
			mappings.push( mapping );
		} );

		// TODO: call REST API
		console.log( mappings );

		// TODO: showError( '' );
		return true;
	}

	function clickSave( event ) {
		event.preventDefault();

		let saved = saveMappings( this );

		if ( !saved ) {
			return;
		}

		let row = findRow( this );
		row.data( 'predicate', row.find( '[name="wikibase-rdf-predicate"] :selected' ).val() );
		row.data( 'object', row.find( '[name="wikibase-rdf-object"]' ).val() );

		row.removeClass( 'wikibase-rdf-row-editing-existing' );
		row.removeClass( 'wikibase-rdf-row-editing-add' );

		row.html( $( '.wikibase-rdf-row-template' ).html() );
		row.find( '.wikibase-rdf-predicate' ).html( row.data( 'predicate' ) );
		row.find( '.wikibase-rdf-object' ).html( row.data( 'object' ) );
	}

	function clickRemove( event ) {
		event.preventDefault();

		let saved = saveMappings( this );

		if ( !saved ) {
			return;
		}

		findRow( this ).remove();
	}

	function clickCancel( event ) {
		event.preventDefault();

		let row = findRow( this );

		if ( row.hasClass( 'wikibase-rdf-row-editing-add' ) ) {
			row.remove();
			return;
		}

		row.html( $( '.wikibase-rdf-row-template' ).html() );
		row.removeClass( 'wikibase-rdf-row-editing' );
		row.find( '.wikibase-rdf-predicate' ).html( row.data( 'predicate' ) );
		row.find( '.wikibase-rdf-object' ).html( row.data( 'object' ) );
	}

	function clickAdd( event ) {
		event.preventDefault();

		let row = $( '.wikibase-rdf-row-editing-template' ).clone();
		row.find( '.wikibase-rdf-action-remove' ).remove();
		row.removeClass( 'wikibase-rdf-row-editing-template' );
		row.addClass( 'wikibase-rdf-row-editing-add' );
		row.appendTo( $( '.wikibase-rdf-rows' ) );
	}

	function showError( error ) {
		// TODO: show error message
		console.error( error );
	}

} );
