<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\EntryPoints\Rest;

use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\LocalizedHttpException;
use MediaWiki\Rest\RequestData;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use RequestContext;
use User;
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

		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => true ],
			]
		);

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
				[ 'predicate' => '', 'object' => 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf' ]
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
				[ 'predicate' => '', 'object' => 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf' ]
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
				[ 'predicate' => '', 'object' => 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf' ]
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

	public function testObjectIsInvalid(): void {
		$response = $this->doSaveMappingsRequest( 'Q1', $this->createInvalidObjectBody() );

		$this->assertSame( 400, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );

		$this->assertArrayHasKey( 'invalidMappings', $data );
		$this->assertSame(
			[
				[ 'predicate' => 'rdf:sameAs', 'object' => 'notUrl' ]
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

		$this->assertSame( 403, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data );
	}

	public function testPermissionDeniedForAnonymousUser(): void {
		$this->setMwGlobals( 'wgGroupPermissions', [ '*' => [ 'edit' => false ] ] );

		$this->setRequestUser( $this->getServiceContainer()->getUserFactory()->newAnonymous() );

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody() );

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionDeniedForUserWithNoGroups(): void {
		$this->setMwGlobals( 'wgGroupPermissions', [ '*' => [ 'edit' => false ] ] );

		$this->setRequestUser( $this->getTestUser()->getUser() );

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody() );

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

		$this->setRequestUser( $this->getTestUser( [ 'user' ] )->getUser() );

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody() );

		$this->assertSame( 403, $response->getStatusCode() );
	}

	public function testPermissionNotDeniedForUserWithAllowedGroup(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => false ],
				'user' => [ 'read' => true, 'edit' => false ],
				'sysop' => [ 'read' => true, 'edit' => true ]
			]
		);

		$this->setRequestUser( $this->getTestSysop()->getUser() );

		$response = $this->doSaveMappingsRequest( 'Q1', $this->createValidBody() );

		$this->assertSame( 204, $response->getStatusCode() );
	}

	public function testSaveFailed(): void {
		// TODO: use ThrowingMappingRepository
	}

	private function createValidBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
		]';
	}

	private function createInvalidJsonBody(): string {
		return '
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
		';
	}

	private function createJsonObjectBody(): string {
		return '{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"}';
	}

	private function createInvalidPredicateKeyBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"notPredicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
		]';
	}

	private function createInvalidObjectKeyBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "notObject": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
		]';
	}

	private function createMissingPredicateBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"object": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
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
			{"predicate": "", "object": "http://www.w3.org/2000/01/rdf-schema#subPropertyOf"}
		]';
	}

	private function createEmptyObjectBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "owl:sameAs", "object": ""}
		]';
	}

	private function createInvalidObjectBody(): string {
		return '[
			{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"},
			{"predicate": "rdf:sameAs", "object": "notUrl"}
		]';
	}

	private function doSaveMappingsRequest( string $entityId, string $body ): ResponseInterface {
		return $this->executeHandler(
			WikibaseRdfExtension::saveMappingsApiFactory(),
			new RequestData( [
				'method' => 'POST',
				'pathParams' => [ 'entity_id' => $entityId ],
				'headers' => [ 'Content-Type' => 'application/json' ],
				'bodyContents' => $body
			] )
		);
	}

	private function setRequestUser( User $user ): void {
		RequestContext::getMain()->setUser( $user );
	}

}
