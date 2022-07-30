<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\MappingListAndId;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
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

		$this->assertEquals(
			[
				new MappingListAndId(
					new ItemId( 'Q99002' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' )
					] )
				),
				new MappingListAndId(
					new ItemId( 'Q99003' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
						new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz1' )
					] )
				),
				new MappingListAndId(
					new PropertyId( 'P99002' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' )
					] )
				),
				new MappingListAndId(
					new PropertyId( 'P99003' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
						new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz1' )
					] )
				)
			],
			$allMappings
		);
	}

//	public function testEntityModified(): void {
//		// TODO - return the same mappings
//	}
//
//	public function testEmptyMappings(): void {
//		// TODO - is slot with empty mappings == entity without the mappings slot ?
//		// i.e. text table contains [] versus nothing
//	}

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

		$this->assertEquals(
			[
				new MappingListAndId(
					new ItemId( 'Q99002' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz2' )
					] )
				),
				new MappingListAndId(
					new ItemId( 'Q99003' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
						new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz1' )
					] )
				),
				new MappingListAndId(
					new PropertyId( 'P99002' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz2' )
					] )
				),
				new MappingListAndId(
					new PropertyId( 'P99003' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
						new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz1' )
					] )
				)
			],
			$allMappings
		);
	}

}
