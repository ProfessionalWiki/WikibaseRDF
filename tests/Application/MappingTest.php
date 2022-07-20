<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\Mapping
 */
class MappingTest extends TestCase {

	public function testGetPredicateBase(): void {
		$mapping = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );

		$this->assertSame(
			'owl',
			$mapping->getPredicateBase()
		);
	}

	public function testGetPredicateLocal(): void {
		$mapping = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );

		$this->assertSame(
			'sameAs',
			$mapping->getPredicateLocal()
		);
	}

}
