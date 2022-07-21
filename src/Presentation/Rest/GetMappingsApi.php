<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation\Rest;

use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use RequestContext;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\ParamValidator\ParamValidator;

class GetMappingsApi extends SimpleHandler {

	public static function factory(): self {
		return new self(
			WikibaseRdfExtension::getInstance()->newEntityIdParser(),
			WikibaseRdfExtension::getInstance()->newMappingRepository(
				RequestContext::getMain()->getUser()
			)
		);
	}

	public function __construct(
		private EntityIdParser $entityIdParser,
		private MappingRepository $mappingRepository
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run( string $entityId ): array {
		// TOOD: This currently involves some magic object to array conversion. Either create the Response manually
		// using MappingListSerializer::toJson(), or else repeat the serialization to array logic.
		return [
			'mappings' => $this->mappingRepository->getMappings( $this->getEntityId( $entityId ) )->asArray()
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

}
