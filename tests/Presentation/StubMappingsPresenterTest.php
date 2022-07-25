<?php

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter
 */
class StubMappingsPresenterTest extends TestCase {

	/**
	 * @return string[]
	 */
	private function getAllowedPredicates(): array {
		return [
			'owl:sameAs',
			'skos:exactMatch',
		];
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
