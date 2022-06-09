<?php

namespace ProfessionalWiki\WikibaseRDF\Tests\Rest\Handler;

use MediaWiki\Rest\RequestData;
use MediaWiki\Rest\ResponseException;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Rest\Handler\Mappings;

/**
 * @covers ProfessionalWiki\WikibaseRDF\Rest\Handler\Mappings
 */
class MappingsTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function testWrongMethod(): void {
        $this->expectException(ResponseException::class);
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'PUT',
				'pathParams' => [ 'entity_id' => 'P1' ],
				'headers' => [ 'content-type' => 'application/json' ]
			] )
		);
	}

	public function testNoParam(): void {
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'GET',
				'pathParams' => [ 'entity_id' => 'P1' ]
			] )
		);
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testEmptyPost(): void {
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'P1' ],
				'headers' => [ 'content-type' => 'application/json' ]
			] )
		);
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testSimplePost(): void {
		$this->assertTrue( true, 'empty test' );
	}
}
