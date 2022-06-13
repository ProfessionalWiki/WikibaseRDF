<?php

namespace ProfessionalWiki\WikibaseRDF\Tests\Rest\Handler;

use Generator;
use MediaWiki\Rest\RequestData;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Rest\HttpException;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Rest\Handler\Mappings;
use Psr\Http\Message\StreamInterface;
use Wikibase\DataModel\Entity;

/**
 * @covers ProfessionalWiki\WikibaseRDF\Rest\Handler\Mappings
 */
class MappingsTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function testWrongMethod(): void {
		$this->expectException( HttpException::class );
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

	protected function registerMap(
		string $entity, string $predicate = null, string $object = null
	): ResponseInterface {
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => $entity ],
				'headers' => [ 'content-type' => 'application/json' ],
				'bodyContents' => "$predicate $object"
			] )
		);
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
		$this->assertInstanceOf( StreamInterface::class, $response->getBody() );
		return $response;
	}

	protected function assertMapContains(
		string $mapping, string $message = "Map contains string"
	): void {
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'GET',
				'headers' => [ 'content-type' => 'application/json' ]
			] )
		);
		$this->assertStringContainsString(
			$mapping, $response->getBody()->getContents(), $message
		);
	}

	public function testEmptyMap(): void {
		$this->expectException( HttpException::class );
		$response = $this->executeHandler(
			Mappings::factory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => "P1" ],
				'headers' => [ 'content-type' => 'application/json' ]
			] )
		);
		$this->assertSame( 422, $response->getStatusCode() );
		$this->assertStringContainsStringIgnoringCase(
			"no mapping provided", $response->getBody()->getContents()
		);
	}

	public function providesTestMappings(): Generator {
		yield [ 'P1', 'owl:sameAs', 'rdfs:class' ];
	}

	/**
	 * @dataProvider providesTestMappings
	 */
	public function testMapCreation(
		string $entity, string $predicate, string $object
	): void {
		$this->registerMap( $entity, $predicate, $object );
		$this->assertMapContains( "$predicate $object", "Map was updated" );
	}
}
