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
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup
 * @group Database
 */
class SqlAllMappingsLookupTest extends WikibaseRdfIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->setAllowedPredicates( [ 'foo:foo', 'foo:bar', 'foo:baz' ] );
	}

	private function newSqlAllMappingsLookup(): SqlAllMappingsLookup {
		return new SqlAllMappingsLookup(
			$this->getDb(),
			WikibaseRdfExtension::SLOT_NAME,
			WikibaseRepo::getEntityIdParser(),
			WikibaseRdfExtension::getInstance()->newMappingListSerializer()
		);
	}

	private function saveTestMappings(): void {
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
		$this->createProperty( new NumericPropertyId( 'P99001' ) );
		$this->createPropertyWithMappings(
			new NumericPropertyId( 'P99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' )
			] )
		);
		$this->createPropertyWithMappings(
			new NumericPropertyId( 'P99003' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
				new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz1' )
			] )
		);
	}

	private function getTestMappingsList(): array {
		return [
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
				new NumericPropertyId( 'P99002' ),
				new MappingList( [
					new Mapping( 'foo:foo', 'https://example.com/#foo1' )
				] )
			),
			new MappingListAndId(
				new NumericPropertyId( 'P99003' ),
				new MappingList( [
					new Mapping( 'foo:foo', 'https://example.com/#foo1' ),
					new Mapping( 'foo:bar', 'https://example.com/#bar1' ),
					new Mapping( 'foo:baz', 'https://example.com/#baz1' )
				] )
			)
		];
	}

	public function testReturnsAllMappings(): void {
		$this->saveTestMappings();

		$this->assertEquals(
			$this->getTestMappingsList(),
			$this->newSqlAllMappingsLookup()->getAllMappings()
		);
	}

	public function testEmptyMappings(): void {
		$this->assertSame(
			[],
			$this->newSqlAllMappingsLookup()->getAllMappings()
		);
	}

	public function testReturnsLatestRevisionsWhenMappingSlotsAreModified(): void {
		$this->saveTestMappings();

		$initialCount = count( $this->newSqlAllMappingsLookup()->getAllMappings() );

		$this->setMappings(
			new ItemId( 'Q99002' ),
			new MappingList( [
				new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
				new Mapping( 'foo:baz', 'https://example.com/#baz2' )
			] )
		);
		$this->setMappings(
			new NumericPropertyId( 'P99002' ),
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
					new NumericPropertyId( 'P99002' ),
					new MappingList( [
						new Mapping( 'foo:foo', 'https://example.com/#foo2' ),
						new Mapping( 'foo:baz', 'https://example.com/#baz2' )
					] )
				),
				new MappingListAndId(
					new NumericPropertyId( 'P99003' ),
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

	public function testReturnsLatestRevisionsWhenMainSlotIsModified(): void {
		$this->saveTestMappings();
		$this->modifyItem( new ItemId( 'Q99002' ), 'NewText' );

		$this->assertEquals(
			$this->getTestMappingsList(),
			$this->newSqlAllMappingsLookup()->getAllMappings()
		);
	}

}
