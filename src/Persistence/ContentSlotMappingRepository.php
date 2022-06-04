<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class ContentSlotMappingRepository implements MappingRepository {

	public function getMappingsFor( EntityId $entityId ): MappingList {
		// TODO
		return new MappingList();
	}

	public function saveEntityMappings( EntityId $entityId, MappingList $mappings ): void {
		// TODO
	}

}
