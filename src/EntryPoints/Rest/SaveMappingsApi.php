<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\BodyValidator;
use MediaWiki\Rest\Validator\JsonBodyValidator;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikimedia\Message\MessageValue;
use Wikimedia\ParamValidator\ParamValidator;

class SaveMappingsApi extends SimpleHandler {

	public function __construct(
		private EntityIdParser $entityIdParser,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	public function run( string $entityId ): Response {
		try {
			$realEntityId = $this->getEntityId( $entityId );
		} catch ( EntityIdParsingException ) {
			return $this->presentInvalidEntityId();
		}

		// TODO: Entity ID is valid, but check if it exists
		// return $this->presentEntityIdNotFound();

		$body = $this->getRequest()->getBody()->getContents();
		$mappings = $this->mappingListSerializer->fromJson( $body );

		$presenter = WikibaseRdfExtension::getInstance()->newRestSaveMappingsPresenter( $this->getResponseFactory() );
		$useCase = WikibaseRdfExtension::getInstance()->newSaveMappingsUseCase( $presenter );
		$useCase->saveMappings( $realEntityId, $mappings );

		return $presenter->getResponse();
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

	public function presentEntityIdNotFound(): Response {
		return $this->getResponseFactory()->createLocalizedHttpError(
			404,
			MessageValue::new( 'wikibase-rdf-entity-id-not-found' ),
		);
	}

	public function presentInvalidEntityId(): Response {
		return $this->getResponseFactory()->createLocalizedHttpError(
			400,
			MessageValue::new( 'wikibase-rdf-entity-id-invalid' ),
		);
	}

}
