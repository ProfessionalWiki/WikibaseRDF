<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\BodyValidator;
use MediaWiki\Rest\Validator\JsonBodyValidator;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\ParamValidator\ParamValidator;

class SaveMappingsApi extends SimpleHandler {

	public function __construct(
		private EntityIdParser $entityIdParser,
		private MappingRepository $repository,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run( string $entityId ): array {
		$body = $this->getRequest()->getBody()->getContents();
		$mappings = $this->mappingListSerializer->fromJson( $body );
		$this->repository->setMappings( $this->getEntityId( $entityId ), $mappings );

		// TOOD: Return setMappings status. And anything else?
		return [
			'mappings' => $mappings->asArray(),
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

	/**
	 * @inheritDoc
	 */
	public function getBodyValidator( $contentType ): BodyValidator {
		if ( $contentType !== 'application/json' ) {
			throw new HttpException(
				"Unsupported Content-Type",
				415,
				[ 'content_type' => $contentType ]
			);
		}

		// TODO: Can we validate the Mapping fields here?
		return new JsonBodyValidator( [] );
	}

	private function getEntityId( string $entityId ): EntityId {
		return $this->entityIdParser->parse( $entityId );
	}

}
