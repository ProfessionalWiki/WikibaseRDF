<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\TestDoubles;

use ProfessionalWiki\WikibaseRDF\Application\EntityMappingsAuthorizer;
use Wikibase\DataModel\Entity\EntityId;

class FailingEntityMappingsAuthorizer implements EntityMappingsAuthorizer {

	public function canEditEntityMappings( EntityId $entityId ): bool {
		return false;
	}

}
