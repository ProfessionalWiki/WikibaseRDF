<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\EntityMappingsAuthorizer;
use User;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Repo\Store\EntityPermissionChecker;

class UserBasedEntityMappingsAuthorizer implements EntityMappingsAuthorizer {

	public function __construct(
		private User $user,
		private EntityPermissionChecker $permissionChecker
	) {
	}

	public function canEditEntityMappings( EntityId $entityId ): bool {
		return $this->permissionChecker->getPermissionStatusForEntityId(
			$this->user,
			EntityPermissionChecker::ACTION_EDIT,
			$entityId
		)->isGood();
	}

}
