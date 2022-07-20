<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;

/**
 * TOOD: Stub presenter using HTML strings
 * TODO: Use Mustache, Twig, or Wikibase string templates?
 * TODO: i18n - is wfMessage() enough?
 */
class StubMappingsPresenter implements MappingsPresenter {

	private string $response;

	public function showMappings( MappingList $mappingList ): void {
		$mappingsHtml = '';

		foreach ( $mappingList->asArray() as $index => $mapping ) {
			// TODO: make first row editable in the stub UI
			if ( $index === 0 ) {
				$mappingsHtml .= $this->createEditableRow( $mapping->predicate, $mapping->object );
			} else {
				$mappingsHtml .= $this->createRow( $mapping->predicate, $mapping->object );
			}
		}

		$this->response = '<div id="wikibase-rdf">'
			. '<div id="wikibase-rdf-toggler"></div>'
			. '<div class id="wikibase-rdf-mappings">'
			. $this->createHeader() . $mappingsHtml . $this->createFooter()
			. '</div>'
			. '</div>';
	}

	private function createHeader(): string {
		return '<div id="wikibase-rdf-header">'
			. '<span class="wikibase-rdf-mappings-predicate-heading">' . wfMessage( 'wikibase-rdf-mappings-predicate-heading' ) . '</span>'
			. '<span class="wikibase-rdf-mappings-object-heading">' . wfMessage( 'wikibase-rdf-mappings-object-heading' ) . '</span>'
			. '<span class="wikibase-rdf-mappings-actions-heading"></span>'
			. '</div>';
	}

	private function createEditableRow( string $relationship, string $url ): string {
		return '<div class="wikibase-rdf-row">'
			. '<div class="wikibase-rdf-predicate"><select><option>' . $relationship . '</option></select></div>'
			. '<div class="wikibase-rdf-object"><input value="' . $url . '"></div>'
			. '<div class="wikibase-rdf-actions">'
			. '<a href="#" class="wikibase-rdf-action-save"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-save' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-remove"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-remove' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-cancel"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-cancel' ) . '</a>'
			. '</div>'
			. '</div>';
	}

	private function createRow( string $relationship, string $url ): string {
		return '<div class="wikibase-rdf-row">'
			. '<div class="wikibase-rdf-predicate">' . $relationship . '</div>'
			. '<div class="wikibase-rdf-object">' . $url . '</div>'
			. '<div class="wikibase-rdf-actions">' . $this->createEditButton() . '</div>'
			. '</div>';
	}

	private function createEditButton(): string {
		return '<a href="#" class="wikibase-rdf-action-edit"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-edit' ) . '</a>';
	}

	private function createFooter(): string {
		return '<div class="wikibase-rdf-footer"><a href="#" class="wikibase-rdf-action-add"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-add' ) . '</a></div>';
	}

	public function getHtml(): string {
		return $this->response;
	}

}
