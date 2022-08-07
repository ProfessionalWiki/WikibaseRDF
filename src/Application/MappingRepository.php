<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use PermissionsError;
use Wikibase\DataModel\Entity\EntityId;

interface MappingRepository {

	public function getMappings( EntityId $entityId ): MappingList;

	/**
	 * @throws PermissionsError
	 */
	public function setMappings( EntityId $entityId, MappingList $mappingList ): void;

}
