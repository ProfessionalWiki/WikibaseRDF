<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository
 */
class InMemoryMappingRepositoryUnitTest extends TestCase {

	public function testReturnsEmptyListForUnknownEntity(): void {
		$repo = new InMemoryMappingRepository();

		$this->assertEquals(
			new MappingList(),
			$repo->getMappings( new ItemId( 'Q2' ) )
		);
	}

	public function testPersistenceRoundTrip(): void {
		$repo = new InMemoryMappingRepository();

		$repo->setMappings(
			new ItemId( 'Q1' ),
			new MappingList( [
				new Mapping( 'owl:sameAs', 'https://example.com/uri/1' )
			] )
		);

		$repo->setMappings(
			new ItemId( 'Q2' ),
			new MappingList( [
				new Mapping( 'owl:sameAs', 'https://example.com/uri/2' )
			] )
		);

		$repo->setMappings(
			new ItemId( 'Q3' ),
			new MappingList( [
				new Mapping( 'owl:sameAs', 'https://example.com/uri/3' )
			] )
		);

		$this->assertEquals(
			new MappingList( [
				new Mapping( 'owl:sameAs', 'https://example.com/uri/2' )
			] ),
			$repo->getMappings( new ItemId( 'Q2' ) )
		);
	}

}
