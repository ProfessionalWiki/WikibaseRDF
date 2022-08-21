<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Title;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository
 * @group Database
 */
class SlotEntityContentRepositoryTest extends WikibaseRdfIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->createItem( new ItemId( 'Q100' ) );
	}

	private function newRepo(): SlotEntityContentRepository {
		return WikibaseRdfExtension::getInstance()->newEntityContentRepository(
			self::getTestUser()->getUser()
		);
	}

	public function testReturnsNullWhenEntityNotFound(): void {
		$this->assertNull(
			$this->newRepo()->getContent( new ItemId( 'Q404' ) )
		);
	}

	public function testReturnsNullWhenSlotNotFound(): void {
		$this->assertNull(
			$this->newRepo()->getContent( new ItemId( 'Q100' ) )
		);
	}

	public function testCanSetContentWhenSlotDoesNotExistYet(): void {
		$repo = $this->newRepo();

		$repo->setContent(
			new ItemId( 'Q100' ),
			new \JsonContent( '{ "foo": 42 }' )
		);

		$this->assertEquals(
			new \JsonContent(
'{
    "foo": 42
}'
			),
			$repo->getContent( new ItemId( 'Q100' ) )
		);
	}

	public function testSettingSlotForNonExistingPageResultsInException(): void {
		$this->expectException( Exception::class );

		$this->newRepo()->setContent(
			new ItemId( 'Q404' ),
			new \JsonContent( '{ "foo": 42 }' )
		);
	}

	public function testSetContentForExistingSlotOverridesPreviousValues(): void {
		$repo = $this->newRepo();

		$repo->setContent(
			new ItemId( 'Q100' ),
			new \JsonContent( '{ "foo": 42, "bar": 9001, "baz": 1337 }' )
		);

		$repo->setContent(
			new ItemId( 'Q100' ),
			new \JsonContent( '{ "foo": 1, "bah": 2 }' )
		);

		$this->assertEquals(
			new \JsonContent(
				'{
    "foo": 1,
    "bah": 2
}'
			),
			$repo->getContent( new ItemId( 'Q100' ) )
		);
	}

	private function getRevisionLookup(): RevisionLookup {
		return MediaWikiServices::getInstance()->getRevisionLookup();
	}

	public function testCanRetrieveRevision(): void {
		$repo = $this->newRepo();

		$repo->setContent(
			new ItemId( 'Q100' ),
			new \JsonContent( '{ "foo": 42 }' )
		);
		$firstRevisionId = $this->getRevisionLookup()->getRevisionByTitle( Title::newFromText( 'Item:Q100' ) )->getId();

		$repo->setContent(
			new ItemId( 'Q100' ),
			new \JsonContent( '{ "foo": 84 }' )
		);
		$latestRevisionId = $this->getRevisionLookup()->getRevisionByTitle( Title::newFromText( 'Item:Q100' ) )->getId();

		$this->assertEquals(
			new \JsonContent(
				'{
    "foo": 42
}'
			),
			$repo->getContent( new ItemId( 'Q100' ), $firstRevisionId )
		);

		$this->assertEquals(
			new \JsonContent(
				'{
    "foo": 84
}'
			),
			$repo->getContent( new ItemId( 'Q100' ), $latestRevisionId )
		);

		$this->assertEquals(
			new \JsonContent(
				'{
    "foo": 84
}'
			),
			$repo->getContent( new ItemId( 'Q100' ) )
		);
	}

}
