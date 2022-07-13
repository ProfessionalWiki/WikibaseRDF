<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository
 * @group Database
 */
class SlotEntityContentRepositoryTest extends \MediaWikiIntegrationTestCase {

	private const SLOT_NAME = 'testslot';

	protected function setUp(): void {
		parent::setUp();

		$this->getServiceContainer()->getSlotRoleRegistry()->defineRoleWithModel( self::SLOT_NAME, CONTENT_MODEL_JSON );
	}

	private function newRepo(): SlotEntityContentRepository {
		return new SlotEntityContentRepository(
			self::getTestUser()->getUser(),
			$this->getServiceContainer()->getWikiPageFactory(),
			WikibaseRepo::getEntityTitleLookup(),
			self::SLOT_NAME
		);
	}

	public function testReturnsNullWhenNotFound(): void {
		$this->assertNull(
			$this->newRepo()->getContent( new ItemId( 'Q100' ) )
		);
	}

	public function testSetAndGetRoundTrip(): void {
		$this->createPersistedItem( new ItemId( 'Q100' ) ); // TODO: is this even needed?
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

	private function createPersistedItem( ItemId $itemId ): void {
		WikibaseRepo::getEntityStore()->saveEntity(
			new Item( $itemId ),
			'',
			self::getTestUser()->getUser()
		);
	}

}
