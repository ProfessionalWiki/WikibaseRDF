<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use Wikibase\DataModel\Entity\EntityId;

interface MappingRepository {

	public function getMappings( EntityId $entityId ): MappingList;

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void;

}
