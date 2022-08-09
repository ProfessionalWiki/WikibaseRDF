<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter
 */
class StubMappingsPresenterTest extends TestCase {

	private function getAllowedPredicates(): PredicateList {
		return new PredicateList( [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'skos:exactMatch' ),
		] );
	}

	public function testMappingValuesAreDisplayed(): void {
		$presenter = new StubMappingsPresenter( $this->getAllowedPredicates() );
		$mapping1 = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
		$mapping2 = new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' );

		$presenter->showMappings(
			new MappingList( [ $mapping1, $mapping2 ] )
		);
		$html = $presenter->getHtml();

		$this->assertStringContainsString( $mapping1->predicate, $html );
		$this->assertStringContainsString( $mapping1->object, $html );
		$this->assertStringContainsString( $mapping2->predicate, $html );
		$this->assertStringContainsString( $mapping2->object, $html );
	}

}
