<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\SaveMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 * @group Database
 */
class SaveMappingsApiTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	private const SLOT_NAME = 'testslot';

	protected function setUp(): void {
		parent::setUp();

		$this->getServiceContainer()->getSlotRoleRegistry()->defineRoleWithModel( self::SLOT_NAME, CONTENT_MODEL_JSON );

		$this->createPersistedItem( new ItemId( 'Q1' ) );
	}

	private function createPersistedItem( ItemId $itemId ): void {
		WikibaseRepo::getEntityStore()->saveEntity(
			new Item( $itemId ),
			'',
			self::getTestUser()->getUser()
		);
	}

	public function testHappyPath(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->getBody()
			] )
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertIsArray( $data['mappings'] );

		$this->assertSame(
			[
				[ 'predicate' => 'owl:sameAs', 'object' => 'http://www.w3.org/2000/01/rdf-schema#subClassOf' ],
				[ 'predicate' => 'owl:sameAs', 'object' => 'owl:subClassOf' ],
				[ 'predicate' => 'foo:bar', 'object' => 'http://example.com' ],
			],
			$data['mappings']
		);
	}

	private function getBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "owl:subClassOf"},
			{"predicate": "foo:bar", "object": "http://example.com"}
		]';
	}

}
