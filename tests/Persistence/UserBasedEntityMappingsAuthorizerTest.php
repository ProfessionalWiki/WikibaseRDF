<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Persistence;

use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\WikibaseRDF\Persistence\UserBasedEntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use User;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Persistence\UserBasedEntityMappingsAuthorizer
 * @group Database
 */
class UserBasedEntityMappingsAuthorizerTest extends WikibaseRdfIntegrationTest {

	public function setUp(): void {
		parent::setUp();
		$this->createItem( new ItemId( 'Q1245' ) );
	}

	private function newAuthorizer( User $user ): UserBasedEntityMappingsAuthorizer {
		return new UserBasedEntityMappingsAuthorizer(
			$user,
			WikibaseRdfExtension::getInstance()->newEntityPermissionChecker( $this->getServiceContainer() )
		);
	}

	public function testAnonymousUserCanEdit(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => true ],
			]
		);

		$authorizer = $this->newAuthorizer( $this->getTestUser()->getUser() );

		$this->assertTrue( $authorizer->canEditEntityMappings( new ItemId( 'Q1245' ) ) );
	}

	public function testAnonymousUserCannotEdit(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => false ],
			]
		);

		$authorizer = $this->newAuthorizer( $this->getTestUser()->getUser() );

		$this->assertFalse( $authorizer->canEditEntityMappings( new ItemId( 'Q1245' ) ) );
	}

	public function testUserWithGroupPermissionCanEdit(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => false ],
				'testGroup' => [ 'read' => true, 'edit' => true ],
			]
		);

		$authorizer = $this->newAuthorizer( $this->getTestUser( [ 'testGroup' ] )->getUser() );

		$this->assertTrue( $authorizer->canEditEntityMappings( new ItemId( 'Q1245' ) ) );
	}

	public function testUserWithoutGroupPermissionCannotEdit(): void {
		$this->setMwGlobals(
			'wgGroupPermissions',
			[
				'*' => [ 'read' => true, 'edit' => false ],
				'testGroup' => [ 'read' => true, 'edit' => false ],
			]
		);

		$authorizer = $this->newAuthorizer( $this->getTestUser( [ 'testGroup' ] )->getUser() );

		$this->assertFalse( $authorizer->canEditEntityMappings( new ItemId( 'Q1245' ) ) );
	}

}
