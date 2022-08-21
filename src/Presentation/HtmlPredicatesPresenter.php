<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use Html;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class HtmlPredicatesPresenter implements PredicatesPresenter {

	private string $response = '';
	private PredicateList $predicates;

	public function presentPredicates( PredicateList $predicates ): void {
		$this->predicates = $predicates;
		$this->response = $this->createIntro() . $this->createList();
	}

	private function createIntro(): string {
		return Html::rawElement( 'p', [], wfMessage( 'wikibase-rdf-config-list-intro' )->text() );
	}

	private function createList(): string {
		$predicates = $this->predicates->asArray();

		if ( count( $predicates ) === 0 ) {
			return Html::element( 'p', [], wfMessage( 'wikibase-rdf-config-list-empty' )->text() );
		}

		return Html::element( 'p', [], wfMessage( 'wikibase-rdf-config-list-heading', count( $predicates ) )->text() )
			. Html::rawElement( 'ul', [], implode( $this->createListItems() ) );
	}

	/**
	 * @return string[]
	 */
	private function createListItems(): array {
		return array_map(
			fn( Predicate $predicate ) => Html::element( 'li', [], $predicate->predicate ),
			$this->predicates->asArray()
		);
	}

	public function getHtml(): string {
		return $this->response;
	}

}
