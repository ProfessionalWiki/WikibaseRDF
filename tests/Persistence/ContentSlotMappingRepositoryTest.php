<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryEntityContentRepository;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository
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
			contentRepository: new InMemoryEntityContentRepository()
		);
	}

}
