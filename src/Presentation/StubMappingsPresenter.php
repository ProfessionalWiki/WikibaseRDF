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

	private string $response = '';

	/**
	 * @param string[] $allowedPredicates
	 */
	public function __construct(
		private array $allowedPredicates
	) {
	}

	public function showMappings( MappingList $mappingList ): void {
		$mappingsHtml = '';

		foreach ( $mappingList->asArray() as $index => $mapping ) {
			$mappingsHtml .= $this->createRow( $mapping->predicate, $mapping->object );
		}

		$this->response = '<div id="wikibase-rdf">'
			. '<div id="wikibase-rdf-toggler"></div>'
			. '<div class id="wikibase-rdf-mappings">'
			. $this->createEditTemplate()
			. $this->createHeader()
			. $mappingsHtml
			. $this->createFooter()
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

	private function createEditTemplate(): string {
		return '<div class="wikibase-rdf-row wikibase-rdf-row-editing">'
			. '<div class="wikibase-rdf-predicate">' . $this->createPredicateSelect() . '</div>'
			. '<div class="wikibase-rdf-object"><input name="wikibase-rdf-object" value="xxx" /></div>'
			. '<div class="wikibase-rdf-actions">'
			. '<a href="#" class="wikibase-rdf-action-save"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-save' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-remove"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-remove' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-cancel"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-cancel' ) . '</a>'
			. '</div>'
			. '</div>';
	}

	private function createPredicateSelect(): string {
		$html = '<select name="wikibase-rdf-predicate">';
		foreach ( $this->allowedPredicates as $predicate ) {
			$html .= '<option value="' . $predicate . '">' . $predicate . '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	private function createRow( string $relationship, string $url ): string {
		return '<div class="wikibase-rdf-row">'
			. '<div class="wikibase-rdf-predicate" data="' . $relationship . '">' . $relationship . '</div>'
			. '<div class="wikibase-rdf-object" data="' . $url .'">' . $url . '</div>'
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
