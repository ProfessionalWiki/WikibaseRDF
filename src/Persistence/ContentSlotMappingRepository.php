<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use JsonContent;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

class ContentSlotMappingRepository implements MappingRepository {

	public function __construct(
		private EntityContentRepository $contentRepository
	) {
	}

	public function getMappingsFor( EntityId $entityId ): MappingList {
		$content = $this->contentRepository->getContent( $entityId );

		if ( $content instanceof JsonContent ) {
			return $this->newMappingListFromJson( $content->getText() );
		}

		return new MappingList();
	}

	private function newMappingListFromJson( string $json ): MappingList {
		// TODO
		return new MappingList();
	}

	public function saveEntityMappings( EntityId $entityId, MappingList $mappings ): void {
		$this->contentRepository->setContent(
			$entityId,
			$this->mappingListToContent( $mappings )
		);
	}

	private function mappingListToContent( MappingList $mappings ): JsonContent {
		// TODO
		return new JsonContent( '{}' );
	}

}
