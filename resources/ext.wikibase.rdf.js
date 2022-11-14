/*
 * JavaScript for WikibaseRDF
 */

$( function () {
	'use strict';

	function moveSection() {
		// Move Mappings before Statements section.
		$( '.wikibase-rdf' ).insertBefore( $( 'h2.wikibase-statements' ) ).show();
	}

	function addToggler() {
		// TODO: toggler state needs to be remembered.
		const toggler = $( '.wikibase-rdf-toggler' ).toggler( {
			$subject: $( '.wikibase-rdf-mappings' ),
			visible: false
		} );
		toggler.find( '.ui-toggler-label' ).text( mw.msg( 'wikibase-rdf-mappings-toggler' ) );
	}

	function findRow( element ) {
		return $( element ).parents( '.wikibase-rdf-row' );
	}

	function showPropertyEditWarning( $anchor ) {
		if ( mw.config.get( 'wgCanonicalNamespace' ) !== 'Property' ) {
			return;
		}

		const version = mw.msg( 'wikibase-rdf-property-edit-warning-version' ),
			cookieKey = 'wikibase-rdf.acknowledge-property-edit',
			optionsKey = 'wikibase-rdf-acknowledge-property-edit';

		if ( mw.cookie.get( cookieKey ) === version ||
			mw.user.options.get( optionsKey ) === version
		) {
			return;
		}

		const messageText = mw.msg( 'wikibase-rdf-property-edit-warning', mw.msg( 'wikibase-rdf-mappings-action-save' ) );

		const $message = $( '<span><p>' + messageText + '</p></span>' )
				.addClass( 'wikibase-rdf-property-edit-warning-container' ),
			$hideMessage = $( '<a>' )
				.text( mw.msg( 'wikibase-rdf-property-edit-warning-acknowledge' ) )
				.appendTo( $message );

		const $messageAnchor = $( '<span>' )
			.appendTo( document.body )
			.toolbaritem()
			.wbtooltip( {
				content: $message,
				permanent: true,
				gravity: 'sw',
				$anchor: $anchor
			} );

		$hideMessage.on( 'click', function ( event ) {
			event.preventDefault();
			$messageAnchor.data( 'wbtooltip' ).degrade( true );
			$( window ).off( '.wikibase-rdf-property-warning' );
			if ( mw.user.isAnon() ) {
				mw.cookie.set( cookieKey, version, { expires: 3 * 365 * 24 * 60 * 60, path: '/' } );
			} else {
				const api = new mw.Api();
				api.saveOption( optionsKey, version );
			}
		} );

		$messageAnchor.data( 'wbtooltip' ).show();

		$( window ).one(
			'scroll.wikibase-rdf-property-warning touchmove.wikibase-rdf-property-warning resize.wikibase-rdf-property-warning',
			function () {
				const tooltip = $messageAnchor.data( 'wbtooltip' );
				if ( tooltip ) {
					$messageAnchor.data( 'wbtooltip' ).hide();
				}
				$( '.wikibase-entityview' ).off( '.wikibase-rdf-property-warning' );
			}
		);
	}

	function clickAdd( event ) {
		event.preventDefault();

		const $row = $( '.wikibase-rdf-row-editing-template' ).clone();
		$row.find( '.wikibase-rdf-action-remove' ).remove();
		$row.removeClass( 'wikibase-rdf-row-editing-template' );
		$row.addClass( 'wikibase-rdf-row' );
		$row.addClass( 'wikibase-rdf-row-editing-add' );
		$row.appendTo( $( '.wikibase-rdf-rows' ) );

		showPropertyEditWarning( $row.find( '.wikibase-rdf-action-save' ) );
	}

	function clickEdit( event ) {
		event.preventDefault();

		const $row = findRow( event.target );
		$row.html( $( '.wikibase-rdf-row-editing-template' ).html() );
		$row.addClass( 'wikibase-rdf-row-editing-existing' );
		$row.find( '[name="wikibase-rdf-predicate"]' ).val( $row.data( 'predicate' ) ).trigger( 'change' );
		$row.find( '[name="wikibase-rdf-object"]' ).val( $row.data( 'object' ) ).trigger( 'change' );

		showPropertyEditWarning( $row.find( '.wikibase-rdf-action-save' ) );
	}

	function hideError() {
		$( '.wikibase-rdf-error' ).hide();
	}

	function showError( error ) {
		$( '.wikibase-rdf-error' ).text( error ).show();
	}

	function createObjectLink( url ) {
		const link = document.createElement( 'a' );
		link.href = url;
		link.text = url;
		link.classList.add( 'external' );
		return $( link );
	}

	function onSuccessfulSave( trigger ) {
		const $row = findRow( trigger );
		$row.data( 'predicate', $row.find( '[name="wikibase-rdf-predicate"]' ).val() );
		$row.data( 'object', $row.find( '[name="wikibase-rdf-object"]' ).val() );

		$row.removeClass( 'wikibase-rdf-row-editing-existing' );
		$row.removeClass( 'wikibase-rdf-row-editing-add' );

		$row.html( $( '.wikibase-rdf-row-template' ).html() );
		$row.find( '.wikibase-rdf-predicate' ).text( $row.data( 'predicate' ) );
		$row.find( '.wikibase-rdf-object' ).append( createObjectLink( $row.data( 'object' ) ) );
	}

	function onSuccessfulRemove( trigger ) {
		findRow( trigger ).remove();
	}

	function disableActions() {
		$( '.wikibase-rdf' ).addClass( 'wikibase-rdf-disabled' );
	}

	function enableActions() {
		$( '.wikibase-rdf' ).removeClass( 'wikibase-rdf-disabled' );
	}

	function saveMappings( trigger ) {
		disableActions();

		const mappings = [];

		findRow( trigger ).addClass( 'wikibase-rdf-row-editing-saving' );

		$( '.wikibase-rdf-rows .wikibase-rdf-row' ).each( function ( index, row ) {
			const mapping = { predicate: '', object: '' };

			const $row = $( row );
			const isAdd = $row.hasClass( 'wikibase-rdf-row-editing-add' );
			const isEdit = $row.hasClass( 'wikibase-rdf-row-editing-existing' );
			const isTrigger = $row.hasClass( 'wikibase-rdf-row-editing-saving' );
			const isRemove = $( trigger ).hasClass( 'wikibase-rdf-action-remove' );

			if ( isTrigger && isRemove ) {
				return;
			} else if ( isAdd || isEdit ) {
				if ( !isTrigger ) {
					return;
				}
				// Rows in edit mode should not be saved, unless it was the triggering row.
				mapping.predicate = $row.find( '[name="wikibase-rdf-predicate"]' ).val();
				mapping.object = $row.find( '[name="wikibase-rdf-object"]' ).val();
				$row.removeClass( 'wikibase-rdf-row-editing-saving' );
			} else {
				mapping.predicate = $row.data( 'predicate' );
				mapping.object = $row.data( 'object' );
			}
			mappings.push( mapping );
		} );

		const api = new mw.Rest();
		api.post(
			'/wikibase-rdf/v0/mappings/' + mw.config.get( 'wgTitle' ),
			mappings,
			{ authorization: 'token' }
		)
			.done( function () {
				hideError();
				const isSave = $( trigger ).hasClass( 'wikibase-rdf-action-save' );
				const isRemove = $( trigger ).hasClass( 'wikibase-rdf-action-remove' );
				if ( isSave ) {
					onSuccessfulSave( trigger );
				} else if ( isRemove ) {
					onSuccessfulRemove( trigger );
				}
				enableActions();
			} )
			.fail( function ( data, response ) {
				const userLang = mw.config.get( 'wgUserLanguage' );
				const siteLang = mw.config.get( 'wgContentLanguage' );
				if ( Object.prototype.hasOwnProperty.call( response.xhr.responseJSON, 'messageTranslations' ) ) {
					if ( userLang in response.xhr.responseJSON.messageTranslations ) {
						showError( response.xhr.responseJSON.messageTranslations[ userLang ] );
					} else if ( siteLang in response.xhr.responseJSON.messageTranslations ) {
						showError( response.xhr.responseJSON.messageTranslations[ siteLang ] );
					} else {
						showError( response.xhr.responseJSON.messageTranslations.en );
					}
				} else {
					// TOOD: Handle other message structures
					showError( JSON.stringify( response.xhr.responseJSON ) );
				}
				enableActions();
			} );
	}

	function clickSave( event ) {
		event.preventDefault();
		saveMappings( event.target );
	}

	function clickRemove( event ) {
		event.preventDefault();
		saveMappings( event.target );
	}

	function clickCancel( event ) {
		event.preventDefault();

		const $row = findRow( event.target );

		if ( $row.hasClass( 'wikibase-rdf-row-editing-add' ) ) {
			$row.remove();
			hideError();
			return;
		}

		$row.html( $( '.wikibase-rdf-row-template' ).html() );
		$row.removeClass( 'wikibase-rdf-row-editing' );
		$row.find( '.wikibase-rdf-predicate' ).text( $row.data( 'predicate' ) );
		$row.find( '.wikibase-rdf-object' ).text( $row.data( 'object' ) );
		hideError();
	}

	function setupEvents() {
		$( '.wikibase-rdf-action-add' ).on( 'click', clickAdd );
		$( '.wikibase-rdf' )
			.on( 'click', '.wikibase-rdf-action-edit', clickEdit )
			.on( 'click', '.wikibase-rdf-action-save', clickSave )
			.on( 'click', '.wikibase-rdf-action-remove', clickRemove )
			.on( 'click', '.wikibase-rdf-action-cancel', clickCancel );
	}

	function init() {
		moveSection();
		mw.loader.using( 'wikibase.view.ControllerViewFactory', addToggler );
		setupEvents();
	}

	init();

} );
