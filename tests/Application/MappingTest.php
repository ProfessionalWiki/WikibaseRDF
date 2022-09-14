<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use InvalidArgumentException;
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

	public function testInvalidPredicateThrowsException(): void {
		$this->expectException( InvalidArgumentException::class );

		new Mapping( 'notValid', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
	}

	public function testInvalidObjectThrowsException(): void {
		$this->expectException( InvalidArgumentException::class );

		new Mapping( 'owl:sameAs', 'notValid' );
	}

}
