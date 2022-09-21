<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\MappingListSerializer
 */
class MappingListSerializerTest extends TestCase {

	public function testMappingListFromArrayWithValidMappings(): void {
		$serializer = new MappingListSerializer();

		$mappings = $serializer->mappingListFromArray( [
			[ 'predicate' => 'foo:Bar', 'object' => 'https://example.com' ],
			[ 'predicate' => 'bar:Baz', 'object' => 'https://test.com' ],
		] );

		$this->assertEquals(
			new MappingList( [
				new Mapping( 'foo:Bar', 'https://example.com' ),
				new Mapping( 'bar:Baz', 'https://test.com' )
			] ),
			$mappings
		);
	}

	public function testMappingListFromArrayWithInvalidMappingsThrowsException(): void {
		$serializer = new MappingListSerializer();

		$this->expectException( InvalidArgumentException::class );

		$serializer->mappingListFromArray( [
			[ 'predicate' => 'foo:Bar', 'object' => 'https://example.com' ],
			[ 'predicate' => 'bar:Baz', 'object' => 'not:valid' ],
		] );
	}

	public function testValidMappingListFromArrayWithValidMappings(): void {
		$serializer = new MappingListSerializer();

		$mappings = $serializer->validMappingListFromArray( [
			[ 'predicate' => 'foo:Bar', 'object' => 'https://example.com' ],
			[ 'predicate' => 'bar:Baz', 'object' => 'https://test.com' ],
		] );

		$this->assertEquals(
			new MappingList( [
				new Mapping( 'foo:Bar', 'https://example.com' ),
				new Mapping( 'bar:Baz', 'https://test.com' )
			] ),
			$mappings
		);
	}

	public function testValidMappingListFromArrayWithInvalidMappingsDoesNotThrowException(): void {
		$serializer = new MappingListSerializer();

		$mappings = $serializer->validMappingListFromArray( [
			[ 'predicate' => 'foo:Bar', 'object' => 'https://example.com' ],
			[ 'predicate' => 'bar:Baz', 'object' => 'not:valid' ],
		] );

		$this->assertEquals(
			new MappingList( [
				new Mapping( 'foo:Bar', 'https://example.com' ),
			] ),
			$mappings
		);
	}

}
