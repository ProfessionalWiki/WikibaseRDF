/*
 * JavaScript for WikibaseRDF
 */

$( function () {
	'use strict';

	moveSection();
	mw.loader.using( 'wikibase.view.ControllerViewFactory', addToggler );
	setupEvents();

	function moveSection() {
		// Move Mappings before Statements section.
		$( '#wikibase-rdf' ).insertBefore( $( 'h2.wikibase-statements' ) ).show();
	}

	function addToggler() {
		// TODO: toggler state needs to be remembered.
		var toggler = $( '#wikibase-rdf-toggler' ).toggler( {
			$subject: $( '#wikibase-rdf-mappings' ),
			visible: false
		} );
		toggler.find( '.ui-toggler-label' ).text( mw.msg( 'wikibase-rdf-mappings-toggler' ) );
	}

	function setupEvents() {
		$( '.wikibase-rdf-action-add' ).click( clickAdd );
		$( '#wikibase-rdf' )
			.on( 'click', '.wikibase-rdf-action-edit', clickEdit )
			.on( 'click', '.wikibase-rdf-action-save', clickSave )
			.on( 'click', '.wikibase-rdf-action-remove', clickRemove )
			.on( 'click', '.wikibase-rdf-action-cancel', clickCancel );
	}

	function findRow( element ) {
		return $( element ).parents( '.wikibase-rdf-row' );
	}

	function clickAdd( event ) {
		console.log( 'clickAdd' );
		event.preventDefault();

		let row = $( '.wikibase-rdf-row-editing-template' ).clone();
		row.find( '.wikibase-rdf-action-remove' ).remove();
		row.removeClass( 'wikibase-rdf-row-editing-template' );
		row.addClass( 'wikibase-rdf-row-editing-add' );
		row.appendTo( $( '.wikibase-rdf-rows' ) );
	}

	function clickEdit( event ) {
		console.log( 'clickEdit' );
		event.preventDefault();

		let row = findRow( event.target );
		row.html( $( '.wikibase-rdf-row-editing-template' ).html() );
		row.addClass( 'wikibase-rdf-row-editing-existing' );
		row.find( '[name="wikibase-rdf-predicate"]' ).val( row.data( 'predicate' ) ).change();
		row.find( '[name="wikibase-rdf-object"]' ).val( row.data( 'object' ) ).change();
	}

	function clickSave( event ) {
		console.log( 'clickSave' );
		event.preventDefault();
		saveMappings( event.target );
	}

	function clickRemove( event ) {
		console.log( 'clickRemove' );
		event.preventDefault();
		saveMappings( event.target );
	}

	function clickCancel( event ) {
		console.log( 'clickCancel' );
		event.preventDefault();

		let row = findRow( event.target );

		if ( row.hasClass( 'wikibase-rdf-row-editing-add' ) ) {
			row.remove();
			return;
		}

		row.html( $( '.wikibase-rdf-row-template' ).html() );
		row.removeClass( 'wikibase-rdf-row-editing' );
		row.find( '.wikibase-rdf-predicate' ).html( row.data( 'predicate' ) );
		row.find( '.wikibase-rdf-object' ).html( row.data( 'object' ) );
	}

	function saveMappings( trigger ) {
		console.log( 'saveMappings' );
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
			} else if ( isAdd || isEdit) {
				if ( !isTrigger ) {
					return;
				}
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

		let api = new mw.Rest();
		api.post(
			'/wikibase-rdf/v0/mappings/' + mw.config.get( 'wgTitle' ),
			mappings,
			{ 'authorization': 'token' }
		)
			.done( function() {
				hideError();
				let isSave = $( trigger ).hasClass( 'wikibase-rdf-action-save' );
				let isRemove = $( trigger ).hasClass( 'wikibase-rdf-action-remove' );
				if ( isSave ) {
					onSuccessfulSave( trigger );
				}
				else if ( isRemove ) {
					onSuccessfulRemove( trigger );
				}
			} )
			.fail ( function( data, response ) {
				showError( response.xhr.responseJSON );
			} );
	}

	function onSuccessfulSave( trigger ) {
		console.log( 'onSuccessfulSave' );
		let row = findRow( trigger );
		row.data( 'predicate', row.find( '[name="wikibase-rdf-predicate"] :selected' ).val() );
		row.data( 'object', row.find( '[name="wikibase-rdf-object"]' ).val() );

		row.removeClass( 'wikibase-rdf-row-editing-existing' );
		row.removeClass( 'wikibase-rdf-row-editing-add' );

		row.html( $( '.wikibase-rdf-row-template' ).html() );
		row.find( '.wikibase-rdf-predicate' ).html( row.data( 'predicate' ) );
		row.find( '.wikibase-rdf-object' ).html( row.data( 'object' ) );
	}

	function onSuccessfulRemove( trigger ) {
		console.log( 'removed' );
		findRow( trigger ).remove();
	}

	function hideError() {
		console.log( 'hideError' );
		$( '.wikibase-rdf-error' ).hide();
	}

	function showError( error ) {
		console.log( 'showError' );
		// TODO: i18n message
		$( '.wikibase-rdf-error' ).html( JSON.stringify( error ) ).show();
	}

} );
