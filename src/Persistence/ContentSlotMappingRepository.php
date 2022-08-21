<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use JsonContent;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityId;

class ContentSlotMappingRepository implements MappingRepository {

	public function __construct(
		private EntityContentRepository $contentRepository,
		private MappingListSerializer $serializer
	) {
	}

	public function getMappings( EntityId $entityId, int $revisionId = 0 ): MappingList {
		$content = $this->contentRepository->getContent( $entityId, $revisionId );

		if ( $content instanceof JsonContent ) {
			return $this->serializer->fromJson( $content->getText() );
		}

		return new MappingList();
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$this->contentRepository->setContent(
			$entityId,
			$this->mappingListToContent( $mappingList )
		);
	}

	private function mappingListToContent( MappingList $mappingList ): JsonContent {
		return new JsonContent( $this->serializer->toJson( $mappingList ) );
	}

}
