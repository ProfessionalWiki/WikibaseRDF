<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseFactory;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;
use Throwable;
use Wikimedia\Message\MessageValue;

class RestSaveMappingsPresenter implements SaveMappingsPresenter {

	private Response $response;

	public function __construct(
		private ResponseFactory $responseFactory
	) {
	}

	public function presentSuccess(): void {
		$this->response = $this->responseFactory->createNoContent();
	}

	public function presentInvalidMappings( array $mappings ): void {
		$this->response = $this->responseFactory->createLocalizedHttpError(
			400,
			MessageValue::new( 'wikibase-rdf-save-mappings-invalid-mappings' ),
			[ 'invalidMappings' => $mappings ]
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

	public function presentPermissionDenied(): void {
		$this->response = $this->responseFactory->createLocalizedHttpError(
			403,
			MessageValue::new( 'wikibase-rdf-permission-denied' ),
		);
	}

	public function getResponse(): Response {
		return $this->response;
	}

}
