<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\Application\AllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Application\MappingListAndId;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;

class GetAllMappingsApi extends SimpleHandler {

	public function __construct(
		private AllMappingsLookup $allMappingsLookup,
		private MappingListSerializer $serializer
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run(): array {
		$mappings = $this->allMappingsLookup->getAllMappings();

		return [
			'mappings' => $this->entityMappingsToArray( $mappings ),
		];
	}

	/**
	 * @param MappingListAndId[] $entityMappingsList
	 *
	 * @return array<string, array<int, array{predicate: string, object: string}>>
	 */
	private function entityMappingsToArray( array $entityMappingsList ): array {
		$array = [];
		foreach ( $entityMappingsList as $entityMappings ) {
			$array[$entityMappings->entityId->getSerialization()] = $this->serializer->mappingListToArray( $entityMappings->mappingList );
		}
		return $array;
	}

}
