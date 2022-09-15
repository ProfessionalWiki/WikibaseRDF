<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryEntityContentRepository;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\InMemoryEntityContentRepository
 */
class ContentSlotMappingRepositoryTest extends TestCase {

	public function testGetMappingsForNonExistingEntity(): void {
		$this->assertEquals(
			new MappingList(),
			$this->newRepo()->getMappings( new ItemId( 'Q404' ) )
		);
	}

	private function newRepo(): ContentSlotMappingRepository {
		return new ContentSlotMappingRepository(
			contentRepository: new InMemoryEntityContentRepository(),
			serializer: new MappingListSerializer()
		);
	}

	public function testPersistenceRoundTrip(): void {
		$repo = $this->newRepo();

		$repo->setMappings(
			new ItemId( 'Q1' ),
			new MappingList( [
				new Mapping( 'owl:q1-predicate-1', 'http://q1-object-1' ),
			] )
		);

		$repo->setMappings(
			new ItemId( 'Q2' ),
			new MappingList( [
				new Mapping( 'owl:q2-predicate-1', 'http://q2-object-1' ),
				new Mapping( 'owl:q2-predicate-2', 'http://q2-object-2' ),
			] )
		);

		$repo->setMappings(
			new ItemId( 'Q3' ),
			new MappingList( [
				new Mapping( 'owl:q3-predicate-1', 'http://q3-object-1' ),
			] )
		);

		$this->assertEquals(
			new MappingList( [
				new Mapping( 'owl:q2-predicate-1', 'http://q2-object-1' ),
				new Mapping( 'owl:q2-predicate-2', 'http://q2-object-2' ),
			] ),
			$repo->getMappings( new ItemId( 'Q2' ) )
		);
	}

}
