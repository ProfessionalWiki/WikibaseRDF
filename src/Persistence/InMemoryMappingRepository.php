<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class InMemoryMappingRepository implements MappingRepository {

	/**
	 * @var array<string, MappingList>
	 */
	private static array $mappingsById = [];

	/**
	 * @return array<string, array<int, string>>
	 */
	public function getAllMappings(): array {
		$ret = [];
		foreach ( self::$mappingsById as $entity => $mapping ) {
			$ret[$entity][] = $entity;
			foreach ( $mapping->asArray() as $entry ) {
				$ret[$entity][] = $entry->predicate . " " . $entry->object;
			}
		}

		return $ret;
	}

	public function getMappingsFor( EntityId $entityId ): MappingList {
		return self::$mappingsById[$entityId->getSerialization()] ?? new MappingList();
	}

	public function saveEntityMappings( EntityId $entityId, MappingList $mappings ): void {
		self::$mappingsById[$entityId->getSerialization()] = $mappings;
	}

}
