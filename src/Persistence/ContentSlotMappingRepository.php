<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use JsonContent;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class ContentSlotMappingRepository implements MappingRepository {

	private const PREDICATE_KEY = 'predicate';
	private const OBJECT_KEY = 'object';

	public function __construct(
		private EntityContentRepository $contentRepository
	) {
	}

	public function getMappings( EntityId $entityId ): MappingList {
		$content = $this->contentRepository->getContent( $entityId );

		if ( $content instanceof JsonContent ) {
			return $this->newMappingListFromJson( $content->getText() );
		}

		return new MappingList();
	}

	private function newMappingListFromJson( string $json ): MappingList {
		$array = json_decode( $json, true );

		if ( is_array( $array ) ) {
			return $this->mappingListFromArray( $array );
		}

		return new MappingList();
	}

	private function mappingListFromArray( array $mappings ): MappingList {
		return new MappingList(
			array_map(
				fn( array $mapping ) => new Mapping(
					predicate: $mapping[self::PREDICATE_KEY],
					object: $mapping[self::OBJECT_KEY]
				),
				$mappings
			)
		);
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$this->contentRepository->setContent(
			$entityId,
			$this->mappingListToContent( $mappingList )
		);
	}

	private function mappingListToContent( MappingList $mappingList ): JsonContent {
		return new JsonContent( json_encode( $this->mappingListToArray( $mappingList ) ) );
	}

	private function mappingListToArray( MappingList $mappings ): array {
		return array_map(
			fn( Mapping $mapping ) => [
				self::PREDICATE_KEY => $mapping->predicate,
				self::OBJECT_KEY => $mapping->object
			],
			$mappings->asArray()
		);
	}

}
