<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\EntryPoints\Rest;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 */
class GetMappingsApiTest extends WikibaseRdfIntegrationTest {
	use HandlerTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->setAllowedPredicates( [ 'owl:sameAs' ] );

		$this->createItem( new ItemId( 'Q1' ) );
	}

	public function testHappyPathWithNoMappings(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::getMappingsApiFactory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'Q1' ] ] )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertSame( [], $data['mappings'] );
	}

	public function testHappyPathWithExistingMappings(): void {
		$this->createItem( new ItemId( 'Q2' ) );
		$this->setMappings(
			new ItemId( 'Q2' ),
			new MappingList( [
				new Mapping( 'owl:sameAs', 'http://example.com' )
			] )
		);

		$response = $this->executeHandler(
			WikibaseRdfExtension::getMappingsApiFactory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'Q2' ] ] )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'owl:sameAs', 'object' => 'http://example.com' ]
			],
			$data['mappings']
		);
	}

	public function testInvalidEntityId(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::getMappingsApiFactory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'NotId' ] ] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertStringContainsString(
			'wikibase-rdf-entity-id-invalid',
			$data['messageTranslations']['']
		);
	}

	public function testMissingEntityId(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::getMappingsApiFactory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'Q1000000000' ] ] )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertSame( [], $data['mappings'] );
	}

}
