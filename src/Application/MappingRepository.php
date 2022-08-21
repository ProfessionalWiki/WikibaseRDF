<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use Wikibase\DataModel\Entity\EntityId;

interface MappingRepository {

	public function getMappings( EntityId $entityId, int $revisionId = 0 ): MappingList;

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void;

}
