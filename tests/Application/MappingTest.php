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

	public function provideValidObjects(): iterable {
		yield 'Standard URL' => [ 'http://www.w3.org/2002/07/owl#sameAs' ];
		yield 'Other protocol' => [ 'ftp://example.com' ];
		yield 'Without TLD' => [ 'http://example' ];
		yield 'Non-ASCII' => [ 'http://exåmple.com' ];
	}

	/**
	 * @dataProvider provideValidObjects
	 */
	public function testValidObject( string $object ): void {
		$mapping = new Mapping( 'owl:sameAs', $object );

		$this->assertSame( $object, $mapping->object );
	}

	public static function provideInvalidObjects(): iterable {
		yield 'Missing protocol' => [ 'example.com' ];
		yield 'Missing slash' => [ 'http:/example.com' ];
	}

	/**
	 * @dataProvider provideInvalidObjects
	 */
	public function testInvalidObjectThrowsException( string $object ): void {
		$this->expectException( InvalidArgumentException::class );

		new Mapping( 'owl:sameAs', $object );
	}

}
