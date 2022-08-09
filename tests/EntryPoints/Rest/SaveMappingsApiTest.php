<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Integration;

use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\LocalizedHttpException;
use MediaWiki\Rest\RequestData;
use MediaWiki\Rest\ResponseInterface;
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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody() );

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testJsonIsInvalid(): void {
		$this->expectException( LocalizedHttpException::class );
		$this->doSaveMappingsRequest( 'Q1', $this->createInvalidJsonBody() );
	}

	public function testJsonIsAnObject(): void {
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createJsonObjectBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', '{}' );

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testJsonIsAnEmptyList(): void {
		$response = $this->doSaveMappingsRequest( 'Q1', '[]' );

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testPredicateKeyIsInvalid(): void {
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createInvalidPredicateKeyBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createInvalidObjectKeyBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createMissingPredicateBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createMissingObjectBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createEmptyPredicateBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createEmptyObjectBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createMalformedPredicateBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createDisallowedPredicateBody() );

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
		$response = $this->doSaveMappingsRequest( 'NotId', $this->createValidBody() );

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
		$response = $this->doSaveMappingsRequest( 'Q1000000000', $this->createValidBody() );

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

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody(), $this->mockAnonNullAuthority() );

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionDeniedForUserWithNoGroups(): void {
		$this->setMwGlobals( 'wgGroupPermissions', [ '*' => [ 'edit' => false ] ] );

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody(), $this->getTestUser()->getUser() );

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

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody(), $this->getTestUser( [ 'user' ] )->getUser() );

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

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody(), $this->getTestSysop()->getUser() );

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

	private function doSaveMappingsRequest( string $entityId, string $body, Authority $authority = null ): ResponseInterface {
		return $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => $entityId ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $body
			] ),
			authority: $authority
		);
	}

}
