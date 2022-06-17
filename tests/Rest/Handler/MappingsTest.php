<?php

namespace ProfessionalWiki\WikibaseRDF\Tests\Rest\Handler;

use Generator;
use MediaWiki\Rest\RequestData;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Rest\HttpException;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Rest\Handler\UpdateMapping;
use ProfessionalWiki\WikibaseRDF\Rest\Handler\GetMappings;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository as MapRepo;
use Psr\Http\Message\StreamInterface;
use Wikibase\DataModel\Entity;

/**
 * @covers ProfessionalWiki\WikibaseRDF\Rest\Handler\UpdateMapping
 * @covers ProfessionalWiki\WikibaseRDF\Rest\Handler\GetMappings
 */
class MappingsTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	protected function makeMapUsable( array $map ) {
		$arrayMap = [];

		foreach ( $map as $key => $val ) {
			$arrayMap[] = [ 'object' => $key, 'predicate' => $val ];
		}
		return $arrayMap;
	}

	protected function registerMap(
		string $entity, array $map
	): ResponseInterface {
		$body = json_encode( [ 'mapping' => $this->makeMapUsable( $map ) ] );
		$request = [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => $entity ],
				'headers' => [ 'content-type' => 'application/json' ],
				'bodyContents' => $body
		];

		$response = $this->executeHandler(
			new UpdateMapping( new MapRepo ),
			new RequestData( $request )
		);
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
		$this->assertInstanceOf( StreamInterface::class, $response->getBody() );
		return $response;
	}

	protected function assertMapExists(
		string $entity, array $mapping, string $message = "Map contains"
	): void {
		$response = $this->executeHandler(
			new GetMappings( new MapRepo ),
			new RequestData( [
				'method' => 'GET',
				'headers' => [ 'content-type' => 'application/json' ]
			] )
		);
		$blob = $response->getBody()->getContents();
		$back = json_decode( $blob, true, 512, JSON_THROW_ON_ERROR );
		$this->assertArrayHasKey( $entity, $back, "Entity ($entity) is in map" );
		$check = $back[$entity];
		$keyAgain = array_shift( $check );
		$this->assertEquals( $entity, $keyAgain );
		if ( count( $check ) ) {
			$this->assertArrayEquals(
				$this->makeMapUsable( $mapping ), $check, false, true, "Entity has mapping"
			);
		}
	}

	public function testEmptyMap(): void {
		$this->expectException( HttpException::class );
		$response = $this->executeHandler(
			new UpdateMapping( new MapRepo ),
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
		yield [ 'P1', [ ] ];
		yield [ 'P1', [ 'owl:sameAs' => 'rdfs:class' ] ];
		yield [ 'P2', [
			'owl:sameAs' => 'rdfs:class',
			'rdfs:InvalidValue' => 'rdfs:InvalidKey',
		] ];
		yield [ 'P1', [
			'rdfs:subClassOf' => 'rdfs:subClassOf',
		] ];
	}

	/**
	 * @dataProvider providesTestMappings
	 */
	public function testMapCreation(
		string $entity, array $map
	): void {
		$this->registerMap( $entity, $map );
		$this->assertMapExists( $entity, $map, "Map was updated" );
	}
}
