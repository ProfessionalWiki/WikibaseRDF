<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\TestDoubles;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use RuntimeException;
use Wikibase\DataModel\Entity\EntityId;

class ThrowingMappingRepository implements MappingRepository {

	public function getMappings( EntityId $entityId, int $revisionId = 0 ): MappingList {
		throw new RuntimeException();
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		throw new RuntimeException();
	}

}
