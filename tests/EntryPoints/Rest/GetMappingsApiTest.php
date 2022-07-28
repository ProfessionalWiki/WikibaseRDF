<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 */
class GetMappingsApiTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function testHappyPath(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::getMappingsApiFactory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'Q1' ] ] )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertIsArray( $data['mappings'] );
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

		// TODO: setup language codes in test and/or do we need to test this?
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

		$this->assertSame( 404, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		// TODO: setup language codes in test and/or do we need to test this?
		$this->assertStringContainsString(
			'wikibase-rdf-entity-id-not-found',
			$data['messageTranslations']['']
		);
	}

}
