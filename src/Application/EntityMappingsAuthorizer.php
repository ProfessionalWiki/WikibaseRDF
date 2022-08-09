<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use Wikibase\DataModel\Entity\EntityId;

interface EntityMappingsAuthorizer {

	public function canEditEntityMappings( EntityId $entityId ): bool;

}
