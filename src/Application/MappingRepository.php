<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use Wikibase\DataModel\Entity\EntityId;

interface MappingRepository {

	public function getMappingsFor( EntityId $entityId ): MappingList;

	public function saveEntityMappings( EntityId $entityId, MappingList $mappings ): void;

}
