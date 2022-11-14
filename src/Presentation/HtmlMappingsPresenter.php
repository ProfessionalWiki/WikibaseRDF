<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use Html;
use Message;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class HtmlMappingsPresenter implements MappingsPresenter {

	private string $response = '';

	public function __construct(
		private PredicateList $allowedPredicates,
		private bool $isDiffPage
	) {
	}

	public function showMappings( MappingList $mappingList, bool $canEdit ): void {
		$this->response = '<div class="wikibase-rdf" style="display: none;">'
			. '<div class="wikibase-rdf-toggler"></div>'
			. '<div class="wikibase-rdf-mappings">'
			. '<table>'
			. $this->createHeader()
			. '<tbody class="wikibase-rdf-rows">'
			. $this->createEditTemplate( $canEdit && !$this->isDiffPage )
			. $this->createRowTemplate( $canEdit && !$this->isDiffPage )
			. $this->createErrorBox()
			. $this->createRows( $mappingList, $canEdit && !$this->isDiffPage )
			. '</tbody>'
			. '</table>'
			. $this->createFooter( $canEdit && !$this->isDiffPage )
			. '</div>'
			. '</div>';
	}

	private function createHeader(): string {
		return '<thead class="wikibase-rdf-header"><tr>'
			. '<th class="wikibase-rdf-mappings-predicate-heading">' . $this->msg( 'wikibase-rdf-mappings-predicate-heading' )->escaped() . '</th>'
			. '<th class="wikibase-rdf-mappings-object-heading">' . $this->msg( 'wikibase-rdf-mappings-object-heading' )->escaped() . '</th>'
			. '<th class="wikibase-rdf-mappings-actions-heading"></th>'
			. '</tr></thead>';
	}

	private function createErrorBox(): string {
		return '<tr><td class="wikibase-rdf-error" style="display: none;" colspan="3"></td></tr>';
	}

	private function createEditTemplate( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<tr class="wikibase-rdf-row-editing-template">'
			. '<td class="wikibase-rdf-predicate">' . $this->createPredicateSelect() . '</td>'
			. '<td class="wikibase-rdf-object"><input name="wikibase-rdf-object" value="" /></td>'
			. '<td class="wikibase-rdf-actions">'
			. '<a href="#" class="wikibase-rdf-action-save"><span class="icon"></span>' . $this->msg( 'wikibase-rdf-mappings-action-save' )->escaped() . '</a> '
			. '<a href="#" class="wikibase-rdf-action-remove"><span class="icon"></span>' . $this->msg( 'wikibase-rdf-mappings-action-remove' )->escaped() . '</a> '
			. '<a href="#" class="wikibase-rdf-action-cancel"><span class="icon"></span>' . $this->msg( 'wikibase-rdf-mappings-action-cancel' )->escaped() . '</a>'
			. '</td>'
			. '</tr>';
	}

	private function createRowTemplate( bool $canEdit ): string {
		return '<tr class="wikibase-rdf-row-template">'
			. '<td class="wikibase-rdf-predicate"></td>'
			. '<td class="wikibase-rdf-object"></td>'
			. '<td class="wikibase-rdf-actions">' . $this->createEditButton( $canEdit ) . '</td>'
			. '</tr>';
	}

	private function createRows( MappingList $mappingList, bool $canEdit ): string {
		$html = '';
		foreach ( $mappingList->asArray() as $mapping ) {
			$html .= $this->createRow( $mapping->predicate, $mapping->object, $canEdit );
		}

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
			'tr',
			[ 'class' => 'wikibase-rdf-row', 'data-predicate' => $relationship, 'data-object' => $url ],
			Html::element( 'td', [ 'class' => 'wikibase-rdf-predicate' ], $relationship )
				. Html::rawElement( 'td', [ 'class' => 'wikibase-rdf-object' ], $this->createObjectLink( $url ) )
				. Html::rawElement( 'td', [ 'class' => 'wikibase-rdf-actions' ], $this->createEditButton( $canEdit ) )
		);
	}

	private function createObjectLink( string $url ): string {
		return Html::element( 'a', [ 'href' => $url, 'rel' => 'nofollow', 'class' => 'external' ], $url );
	}

	private function createEditButton( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<a href="#" class="wikibase-rdf-action-edit"><span class="icon"></span>' . $this->msg( 'wikibase-rdf-mappings-action-edit' )->escaped() . '</a>';
	}

	private function createFooter( bool $canEdit ): string {
		if ( !$canEdit ) {
			return '';
		}
		return '<div class="wikibase-rdf-footer"><div class="wikibase-rdf-footer-actions">'
			. '<a href="#" class="wikibase-rdf-action-add"><span class="icon"></span>' . $this->msg( 'wikibase-rdf-mappings-action-add' )->escaped() . '</a>'
			. '</div></div>';
	}

	public function getHtml(): string {
		return $this->response;
	}

	private function msg( string $key, mixed ...$params ): Message {
		return new Message( $key, $params );
	}

}
