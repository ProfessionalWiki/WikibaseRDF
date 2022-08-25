<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\MappingListAndId;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup
 * @group Database
 * @group Performance
 */
class SqlAllMappingsLookupPerformanceTest extends WikibaseRdfIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->setAllowedPredicates( [ 'foo:bar' ] );
	}

	private function newSqlAllMappingsLookup(): SqlAllMappingsLookup {
		return new SqlAllMappingsLookup(
			$this->db,
			WikibaseRdfExtension::SLOT_NAME,
			WikibaseRepo::getEntityIdParser(),
			WikibaseRdfExtension::getInstance()->newMappingListSerializer()
		);
	}

	public function providePerformance(): iterable {
		// Items, Mappings, Revisions, Expected Milliseconds
		yield [ 1, 1, 1, 1 ];
		yield [ 500, 1, 1, 10 ];
		yield [ 1, 500, 1, 5 ];
		yield [ 1, 1, 500, 1 ];
		yield [ 50, 50, 50, 15 ];
		yield [ 500, 100, 1, 100 ];
	}

	/**
	 * @dataProvider providePerformance
	 */
	public function testPerformance( int $items, int $mappings, int $revisions, int $expectedTime ): void {
		print( "\n$items items with $mappings mappings and $revisions revisions\n" );

		$setupStart = hrtime( true );
		for ( $i = 1; $i <= $items; $i++ ) {
			$id = new ItemId( "Q$i" );
			$this->createItem( $id );
			$this->setMappingsRepeatedly( $id, $mappings, $revisions );
		}

		$lookup = $this->newSqlAllMappingsLookup();

		$setupEnd = hrtime( true );
		$setupTook = ( $setupEnd - $setupStart ) / 1000000000;
		print( "Setup: {$setupTook}s\n" );

		$start = hrtime( true );
		$allMappings = $lookup->getAllMappings();
		$end = hrtime( true );
		$took = ( $end - $start ) / 1000000;

		print( "Get all mappings: {$took}ms\n" );

		$this->assertSame(
			$items * $mappings,
			array_sum(
				array_map( fn( MappingListAndId $mapping ) => count( $mapping->mappingList->asArray() ), $allMappings )
			)
		);
		$this->assertLessThan( $expectedTime, $took );
	}

	private function createBulkMappings( int $count, int $revision ): MappingList {
		return new MappingList(
			array_map(
				fn( $i ) => new Mapping( 'foo:bar', "http://example.com/$i-$revision" ),
				range( 0, $count - 1 )
			)
		);
	}

	private function setMappingsRepeatedly( EntityId $entityId, int $mappings, int $revisions ): void {
		for ( $j = 0; $j < $revisions; $j++ ) {
			$mappingList = $this->createBulkMappings( $mappings, $j );
			$this->setMappings( $entityId, $mappingList );
		}
	}

	protected function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$user = self::getTestSysop()->getUser();
		WikibaseRdfExtension::getInstance()->newMappingRepository( $user )->setMappings( $entityId, $mappingList );
	}

}
