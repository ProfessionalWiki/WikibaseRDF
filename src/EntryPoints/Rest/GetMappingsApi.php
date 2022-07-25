<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\ParamValidator\ParamValidator;

class GetMappingsApi extends SimpleHandler {

	public function __construct(
		private EntityIdParser $entityIdParser,
		private MappingRepository $mappingRepository,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run( string $entityId ): array {
		return [
			'mappings' => $this->getMappingsArray( $this->getMappings( $entityId ) )
		];
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

	private function getMappings( string $entityId ): MappingList {
		return $this->mappingRepository->getMappings( $this->getEntityId( $entityId ) );
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function getMappingsArray( MappingList $mappings ): array {
		return $this->mappingListSerializer->mappingListToArray( $mappings );
	}

}
