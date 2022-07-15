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

	public function getMappings( EntityId $entityId ): MappingList {
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

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$this->contentRepository->setContent(
			$entityId,
			$this->mappingListToContent( $mappingList )
		);
	}

	private function mappingListToContent( MappingList $mappings ): JsonContent {
		// TODO
		return new JsonContent( '{}' );
	}

}
