<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Rest\LocalizedHttpException;
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
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testJsonIsInvalid(): void {
		$this->expectException( LocalizedHttpException::class );
		$this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createInvalidJsonBody()
			] )
		);
	}

	public function testJsonIsAnObject(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createJsonObjectBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	/**
	 * This result is the same as testJsonIsAnEmptyList() because it's decoded as an empty list.
	 * @see JsonBodyValidator::validateBody()
	 */
	public function testJsonIsAnEmptyObject(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => '{}'
			] )
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testJsonIsAnEmptyList(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => '[]'
			] )
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testPredicateKeyIsInvalid(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createInvalidPredicateKeyBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => '', 'object' => 'owl:subClassOf' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testObjectKeyIsInvalid(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createInvalidObjectKeyBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'owl:sameAs', 'object' => '' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testPredicateIsMissing(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createMissingPredicateBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => '', 'object' => 'owl:subClassOf' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testObjectIsMissing(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createMissingObjectBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'owl:sameAs', 'object' => '' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testPredicateIsEmpty(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createEmptyPredicateBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => '', 'object' => 'owl:subClassOf' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testObjectIsEmpty(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createEmptyObjectBody()
			] )
		);

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'owl:sameAs', 'object' => '' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testMappingPredicateIsMalformed(): void {
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
				[ 'predicate' => 'owl-sameAs', 'object' => 'http://example.com' ]
			],
			$data['invalidMappings']
		);

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testMappingPredicateIsNotAllowed(): void {
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

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-invalid-mappings',
			$data['messageTranslations']['']
		);
	}

	public function testEntityIdIsInvalid(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'NotId' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
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

	public function testEntityDoesNotExist(): void {
		$response = $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1000000000' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] )
		);

		$this->assertSame( 500, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertStringContainsString(
			'wikibase-rdf-save-mappings-save-failed',
			$data['messageTranslations']['']
		);
	}

	public function testPermissionDeniedForAnonymousUser(): void {
		$this->setMwGlobals( 'wgGroupPermissions', [ '*' => [ 'edit' => false ] ] );

		$response = $this->executeHandler(
			handler: WikibaseRdfExtension::saveMappingsApiFactory(),
			request: new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] ),
			authority: $this->mockAnonNullAuthority()
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionDeniedForUserWithNoGroups(): void {
		$this->setMwGlobals( 'wgGroupPermissions', [ '*' => [ 'edit' => false ] ] );

		$response = $this->executeHandler(
			handler: WikibaseRdfExtension::saveMappingsApiFactory(),
			request: new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] ),
			authority: $this->getTestUser()->getUser()
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionDeniedForUserWithDeniedGroup(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'edit' => false ],
				'user' => [ 'edit' => false ]
			]
		);

		$response = $this->executeHandler(
			handler: WikibaseRdfExtension::saveMappingsApiFactory(),
			request: new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] ),
			authority: $this->getTestUser( [ 'user' ] )->getUser()
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionNotDeniedForUserWithAllowedGroup(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'edit' => false ],
				'user' => [ 'edit' => false ],
				'sysop' => [ 'edit' => true ]
			]
		);

		$response = $this->executeHandler(
			handler: WikibaseRdfExtension::saveMappingsApiFactory(),
			request: new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => 'Q1' ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $this->createValidBody()
			] ),
			authority: $this->getTestSysop()->getUser()
		);

		$this->assertSame( 204, $response->getStatusCode() );
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

	private function createInvalidJsonBody(): string {
		return '
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "owl:subClassOf"}
		';
	}

	private function createJsonObjectBody(): string {
		return '{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"}';
	}

	private function createInvalidPredicateKeyBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"notPredicate": "owl:sameAs", "object": "owl:subClassOf"}
		]';
	}

	private function createInvalidObjectKeyBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "notObject": "owl:subClassOf"}
		]';
	}

	private function createMissingPredicateBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"object": "owl:subClassOf"}
		]';
	}

	private function createMissingObjectBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs"}
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

	private function createEmptyPredicateBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "", "object": "owl:subClassOf"}
		]';
	}

	private function createEmptyObjectBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": ""}
		]';
	}

}
