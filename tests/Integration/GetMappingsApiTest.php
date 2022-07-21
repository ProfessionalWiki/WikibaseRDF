<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Presentation\Rest\GetMappingsApi;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\Rest\GetMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 */
class GetMappingsApiTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function testHappyPath(): void {
		$response = $this->executeHandler(
			GetMappingsApi::factory(),
			new RequestData( [ 'pathParams' => [ 'entity_id' => 'Q1' ] ] )
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'mappings', $data );
		$this->assertIsArray( $data['mappings'] );
	}

}