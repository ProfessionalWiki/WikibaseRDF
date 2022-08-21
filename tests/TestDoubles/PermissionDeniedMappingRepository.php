<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\TestDoubles;

use PermissionsError;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class PermissionDeniedMappingRepository implements MappingRepository {

	public function getMappings( EntityId $entityId, int $revisionId = 0 ): MappingList {
		throw new PermissionsError( 'edit' );
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		throw new PermissionsError( 'edit' );
	}

}
