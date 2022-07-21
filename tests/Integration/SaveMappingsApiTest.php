<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Presentation\Rest\SaveMappingsApi;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\Rest\SaveMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 */
class SaveMappingsApiTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function setUp(): void {
		// TODO: Create Item
	}

	public function testHappyPath(): void {
		// TOOD: Create Item
//		$response = $this->executeHandler(
//			SaveMappingsApi::factory(),
//			new RequestData( [
//				'method' => 'POST',
//				'pathParams' => [ 'entity_id' => 'Q1' ],
//				'headers' => [ 'Content-Type' => 'application/json'],
//				'bodyContents' => $this->getBody()
//			] )
//		);
//
//		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
//
//		$data = json_decode( $response->getBody()->getContents(), true );
//		$this->assertIsArray( $data );
//
//		$this->assertArrayHasKey( 'mappings', $data );
//		$this->assertIsArray( $data['mappings'] );
	}

	private function getBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "owl:subClassOf"},
			{"predicate": "foo:bar", "object": "http://example.com"}
		]';
	}

}
