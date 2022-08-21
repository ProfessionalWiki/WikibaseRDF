<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Presentation\HtmlPredicatesPresenter;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\HtmlPredicatesPresenter
 */
class HtmlPredicatesPresenterTest extends TestCase {

	public function testHtmlContainsPredicates(): void {
		$mappingList = new PredicateList( [ new Predicate( 'foo:bar' ), new Predicate( 'bar:baz' ) ] );
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates( $mappingList );

		$html = $presenter->getHtml();

		$this->assertStringContainsString( 'foo:bar', $html );
		$this->assertStringContainsString( 'bar:baz', $html );
	}

	public function testHtmlContainsMessageIfNoPredicatesAreConfigured(): void {
		$mappingList = new PredicateList();
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates( $mappingList );

		$html = $presenter->getHtml();

		$this->assertStringContainsString(
			'No predicates are defined.',
			$html
		);
	}

}
