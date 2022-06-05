<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class InMemoryMappingRepository implements MappingRepository {

	/**
	 * @var array<string, MappingList>
	 */
	private array $mappingsById = [];

	public function getMappingsFor( EntityId $entityId ): MappingList {
		return $this->mappingsById[$entityId->getSerialization()] ?? new MappingList();
	}

	public function saveEntityMappings( EntityId $entityId, MappingList $mappings ): void {
		$this->mappingsById[$entityId->getSerialization()] = $mappings;
	}

}
