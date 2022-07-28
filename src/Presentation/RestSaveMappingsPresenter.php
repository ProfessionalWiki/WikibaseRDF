<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseFactory;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikimedia\Message\MessageValue;

class RestSaveMappingsPresenter implements SaveMappingsPresenter {

	private Response $response;

	public function __construct(
		private ResponseFactory $responseFactory,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	public function presentSuccess(): void {
		$this->response = $this->responseFactory->createNoContent();
	}

	public function presentInvalidMappings( MappingList $mappings ): void {
		$this->response = $this->responseFactory->createLocalizedHttpError(
			400,
			MessageValue::new( 'wikibase-rdf-save-mappings-invalid-mappings' ),
			[ 'invalidMappings' => $this->mappingListSerializer->mappingListToArray( $mappings ) ]
		);
	}

	public function presentSaveFailed(): void {
		$this->response = $this->responseFactory->createLocalizedHttpError(
			500,
			MessageValue::new( 'wikibase-rdf-save-mappings-save-failed' )
		);
	}

	public function presentInvalidEntityId(): void {
		$this->response = $this->responseFactory->createLocalizedHttpError(
			400,
			MessageValue::new( 'wikibase-rdf-entity-id-invalid' ),
		);
	}

	public function getResponse(): Response {
		return $this->response;
	}

}
