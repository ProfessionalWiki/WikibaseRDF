<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\SaveMappingsApi
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\RestSaveMappingsPresenter
 * @covers \ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension
 * @group Database
 */
class SaveMappingsApiTest extends WikibaseRdfIntegrationTest {
	use HandlerTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->setAllowedPredicates( [ 'owl:sameAs' ] );

		$this->createItem( new ItemId( 'Q1' ) );
	}

	public function testHappyPath(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'PUT',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testMalformedPredicate(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createMalformedPredicateBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				// TODO
//				[ 'predicate' => 'owl-sameAs', 'object' => 'http://example.com' ]
			],
			$data['invalidMappings']
		);

		// TODO: setup language codes in test and/or do we need to test this?
		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testDisallowedPredicate(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createDisallowedPredicateBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'foo:bar', 'object' => 'http://example.com' ]
			],
			$data['invalidMappings']
		);

		// TODO: setup language codes in test and/or do we need to test this?
		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testInvalidEntityId(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'PUT',
				'pathParams' => [ 'entity_id' => 'NotId' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
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
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'PUT',
				'pathParams' => [ 'entity_id' => 'Q1000000000' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
		);

		$this->assertSame( 500, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		// TODO: setup language codes in test and/or do we need to test this?
		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-save-failed',
			$data['messageTranslations']['']
		);
	}

	public function testSaveFailed(): void {
		// TODO: use ThrowingMappingRepository
	}

	private function createValidBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "owl:subClassOf"}
		]';
	}

	private function createMalformedPredicateBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl-sameAs", "object": "http://example.com"}
		]';
	}

	private function createDisallowedPredicateBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "foo:bar", "object": "http://example.com"}
		]';
	}

}
