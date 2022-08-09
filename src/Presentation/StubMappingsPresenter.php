<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use Html;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class StubMappingsPresenter implements MappingsPresenter {

	private string $response = '';

	public function __construct(
		private PredicateList $allowedPredicates
	) {
	}

	public function showMappings( MappingList $mappingList, bool $canEdit ): void {
		$this->response = '<div id="wikibase-rdf" style="display: none;">'
			. '<div id="wikibase-rdf-toggler"></div>'
			. '<div class id="wikibase-rdf-mappings">'
			. $this->createEditTemplate( $canEdit )
			. $this->createRowTemplate( $canEdit )
			. $this->createHeader()
			. $this->createErrorBox()
			. $this->createRows( $mappingList, $canEdit )
			. $this->createFooter( $canEdit )
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

	private function createErrorBox(): string {
		return '<div class="wikibase-rdf-error" style="display: none;"></div>';
	}

	private function createEditTemplate( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<div class="wikibase-rdf-row wikibase-rdf-row-editing-template">'
			. '<div class="wikibase-rdf-predicate">' . $this->createPredicateSelect() . '</div>'
			. '<div class="wikibase-rdf-object"><input name="wikibase-rdf-object" value="" /></div>'
			. '<div class="wikibase-rdf-actions">'
			. '<a href="#" class="wikibase-rdf-action-save"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-save' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-remove"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-remove' ) . '</a> '
			. '<a href="#" class="wikibase-rdf-action-cancel"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-cancel' ) . '</a>'
			. '</div>'
			. '</div>';
	}

	private function createRowTemplate( bool $canEdit ): string {
		return '<div class="wikibase-rdf-row-template">'
			. '<div class="wikibase-rdf-predicate"></div>'
			. '<div class="wikibase-rdf-object"></div>'
			. '<div class="wikibase-rdf-actions">' . $this->createEditButton( $canEdit ) . '</div>'
			. '</div>';
	}

	private function createRows( MappingList $mappingList, bool $canEdit ): string {
		$html = '<div class="wikibase-rdf-rows">';
		foreach ( $mappingList->asArray() as $mapping ) {
			$html .= $this->createRow( $mapping->predicate, $mapping->object, $canEdit );
		}
		$html .= '</div>';

		return $html;
	}

	private function createPredicateSelect(): string {
		$html = '<select name="wikibase-rdf-predicate">';
		foreach ( $this->allowedPredicates->asArray() as $predicate ) {
			$html .= Html::element( 'option', [ 'value' => $predicate->predicate ], $predicate->predicate );
		}
		$html .= '</select>';
		return $html;
	}

	private function createRow( string $relationship, string $url, bool $canEdit ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'wikibase-rdf-row', 'data-predicate' => $relationship, 'data-object' => $url ],
			Html::element( 'div', [ 'class' => 'wikibase-rdf-predicate' ], $relationship )
				. Html::element( 'div', [ 'class' => 'wikibase-rdf-object' ], $url )
				. Html::rawElement( 'div', [ 'class' => 'wikibase-rdf-actions' ], $this->createEditButton( $canEdit ) )
		);
	}

	private function createEditButton( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<a href="#" class="wikibase-rdf-action-edit"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-edit' ) . '</a>';
	}

	private function createFooter( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<div class="wikibase-rdf-footer"><a href="#" class="wikibase-rdf-action-add"><span class="icon"></span>' . wfMessage( 'wikibase-rdf-mappings-action-add' ) . '</a></div>';
	}

	public function getHtml(): string {
		return $this->response;
	}

}
