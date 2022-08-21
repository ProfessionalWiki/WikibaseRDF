<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use Html;
use Message;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class HtmlPredicatesPresenter implements PredicatesPresenter {

	private const ASTERISK = '<code>*</code>';

	private string $response = '';
	private PredicateList $localSettingsPredicates;
	private PredicateList $wikiPredicates;

	public function presentPredicates( PredicateList $localSettingsPredicates, PredicateList $wikiPredicates ): void {
		$this->localSettingsPredicates = $localSettingsPredicates;
		$this->wikiPredicates = $wikiPredicates;
		$this->response = $this->createIntro() . $this->createList() . $this->createFooter();
	}

	private function createIntro(): string {
		return Html::rawElement( 'p', [], $this->msg( 'wikibase-rdf-config-list-intro' )->text() );
	}

	private function createList(): string {
		$localSettingsPredicates = $this->localSettingsPredicates->asArray();
		$wikiPredicates = $this->wikiPredicates->asArray();
		$count = count( $localSettingsPredicates ) + count( $wikiPredicates );

		if ( $count === 0 ) {
			return Html::element( 'p', [], $this->msg( 'wikibase-rdf-config-list-empty' )->text() );
		}

		return Html::element( 'p', [], $this->msg( 'wikibase-rdf-config-list-heading', $count )->text() )
			. Html::rawElement(
				'ul',
				[],
				implode( $this->createLocalSettingsListItems() ) . implode( $this->createWikiListItems() )
			);
	}

	/**
	 * @return string[]
	 */
	private function createLocalSettingsListItems(): array {
		return array_map(
			static function ( Predicate $predicate ) {
				return Html::rawElement(
					'li',
					[],
					Html::element( 'span', [], $predicate->predicate ) . ' ' . self::ASTERISK
				);
			},
			$this->localSettingsPredicates->asArray()
		);
	}

	/**
	 * @return string[]
	 */
	private function createWikiListItems(): array {
		return array_map(
			fn( Predicate $predicate ) => Html::element( 'li', [], $predicate->predicate ),
			$this->wikiPredicates->asArray()
		);
	}

	private function createFooter(): string {
		if ( count( $this->localSettingsPredicates->asArray() ) === 0 ) {
			return '';
		}

		return Html::rawElement(
			'p',
			[],
			self::ASTERISK . ' ' . $this->msg( 'wikibase-rdf-config-list-footer' )->text()
		);
	}

	public function getHtml(): string {
		return $this->response;
	}

	private function msg( string $key, mixed ...$params ): Message {
		return new Message( $key, $params );
	}

}
