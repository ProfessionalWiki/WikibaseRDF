<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\EntityMappingList;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup
 * @group Database
 */
class SqlAllMappingsLookupTest extends WikibaseRdfIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->setAllowedPredicates( [ 'foo:foo', 'foo:bar', 'foo:baz' ] );

		$this->createItem( new ItemId( 'Q99001' ) );
		$this->createItemWithMappings(
			new ItemId( 'Q99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' )
			] )
		);
		$this->createItemWithMappings(
			new ItemId( 'Q99003' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			] )
		);
		$this->createProperty( new PropertyId( 'P99001' ) );
		$this->createPropertyWithMappings(
			new PropertyId( 'P99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' )
			] )
		);
		$this->createPropertyWithMappings(
			new PropertyId( 'P99003' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			] )
		);
	}

	/**
	 * @param string[] $predicates
	 */
	private function setAllowedPredicates( array $predicates ): void {
		$this->setMwGlobals( 'wgWikibaseRdfPredicates', $predicates );
	}

	public function newSqlAllMappingsLookup(): SqlAllMappingsLookup {
		return new SqlAllMappingsLookup(
			$this->db,
			WikibaseRdfExtension::SLOT_NAME,
			WikibaseRepo::getEntityIdParser(),
			WikibaseRdfExtension::getInstance()->newMappingListSerializer()
		);
	}

	public function testUnmodifiedMappings(): void {
		$allMappings = $this->newSqlAllMappingsLookup()->getAllMappings();

		// TODO: database is polluted by other tests
		// $this->assertCount( 4, $allMappings );

		$this->assertNull(
			$this->getMappingsForEntityId( new ItemId( 'Q99001' ), $allMappings )
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' )
			],
			$this->getMappingsForEntityId( new ItemId( 'Q99002' ), $allMappings )->asArray()
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			],
			$this->getMappingsForEntityId( new ItemId( 'Q99003' ), $allMappings )->asArray()
		);

		$this->assertNull(
			$this->getMappingsForEntityId( new PropertyId( 'P99001' ), $allMappings )
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' )
			],
			$this->getMappingsForEntityId( new PropertyId( 'P99002' ), $allMappings )->asArray()
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			],
			$this->getMappingsForEntityId( new PropertyId( 'P99003' ), $allMappings )->asArray()
		);
	}

	/**
	 * @param EntityMappingList[] $allMappings
	 */
	private function getMappingsForEntityId( EntityId $entityId, array $allMappings ): ?MappingList {
		foreach ( $allMappings as $entityMappings ) {
			if ( $entityMappings->entityId->equals( $entityId ) ) {
				return $entityMappings->mappingList;
			}
		}
		return null;
	}

	public function testEntityModified(): void {
		// TODO - return the same mappings
	}

	public function testEmptyMappings(): void {
		// TODO - is slot with empty mappings == entity without the mappings slot ?
		// i.e. text table contains [] versus nothing
	}

	public function testMappingsModified(): void {
		$initialCount = count( $this->newSqlAllMappingsLookup()->getAllMappings() );

		$this->setMappings(
			new ItemId( 'Q99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz2' )
			] )
		);
		$this->setMappings(
			new PropertyId( 'P99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz2' )
			] )
		);

		$allMappings = $this->newSqlAllMappingsLookup()->getAllMappings();

		$this->assertCount( $initialCount, $allMappings );

		$this->assertNull(
			$this->getMappingsForEntityId( new ItemId( 'Q99001' ), $allMappings )
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz2' )
			],
			$this->getMappingsForEntityId( new ItemId( 'Q99002' ), $allMappings )->asArray()
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			],
			$this->getMappingsForEntityId( new ItemId( 'Q99003' ), $allMappings )->asArray()
		);

		$this->assertNull(
			$this->getMappingsForEntityId( new PropertyId( 'P99001' ), $allMappings )
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz2' )
			],
			$this->getMappingsForEntityId( new PropertyId( 'P99002' ), $allMappings )->asArray()
		);
		$this->assertEquals(
			[
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			],
			$this->getMappingsForEntityId( new PropertyId( 'P99003' ), $allMappings )->asArray()
		);
	}

}
