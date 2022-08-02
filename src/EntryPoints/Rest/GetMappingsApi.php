<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikimedia\Message\MessageValue;
use Wikimedia\ParamValidator\ParamValidator;

class GetMappingsApi extends SimpleHandler {

	public function __construct(
		private EntityIdParser $entityIdParser,
		private MappingRepository $mappingRepository,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	public function run( string $entityId ): Response {
		try {
			$realEntityId = $this->getEntityId( $entityId );
		} catch ( EntityIdParsingException ) {
			return $this->presentInvalidEntityId();
		}

		return $this->presentMappings( $this->getMappings( $realEntityId ) );
	}

	/**
	 * @inheritDoc
	 */
	public function needsWriteAccess() {
		return false;
	}

	/**
	 * @inheritDoc
	 * @return array<string, array<string, mixed>>
	 */
	public function getParamSettings(): array {
		return [
			'entity_id' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	private function getEntityId( string $entityId ): EntityId {
		return $this->entityIdParser->parse( $entityId );
	}

	private function getMappings( EntityId $entityId ): MappingList {
		return $this->mappingRepository->getMappings( $entityId );
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function getMappingsArray( MappingList $mappings ): array {
		return $this->mappingListSerializer->mappingListToArray( $mappings );
	}

	public function presentInvalidEntityId(): Response {
		return $this->getResponseFactory()->createLocalizedHttpError(
			400,
			MessageValue::new( 'wikibase-rdf-entity-id-invalid' ),
		);
	}

	public function presentMappings( MappingList $mappings ): Response {
		return $this->getResponseFactory()->createJson( [
			'mappings' => $this->getMappingsArray( $mappings )
		] );
	}

}
