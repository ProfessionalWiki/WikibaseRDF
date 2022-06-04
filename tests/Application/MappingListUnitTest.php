<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\MappingList
 */
class MappingListUnitTest extends TestCase {

	public function testCanConstructEmptyList(): void {
		$list = new MappingList();

		$this->assertSame( [], $list->asArray() );
	}

	public function testCanAccessMappingsAsArray(): void {
		$mappingArray = [
			new Mapping( 'owl:sameAs', 'https://example.com/uri/1' ),
			new Mapping( 'rdfs:subClassOf', 'https://example.com/uri/2' )
		];

		$list = new MappingList( $mappingArray );

		$this->assertEquals( $mappingArray, $list->asArray() );
	}

}
