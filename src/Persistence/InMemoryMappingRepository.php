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

	public function getMappings( EntityId $entityId, int $revisionId = 0 ): MappingList {
		return $this->mappingsById[$entityId->getSerialization()] ?? new MappingList();
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$this->mappingsById[$entityId->getSerialization()] = $mappingList;
	}

}
