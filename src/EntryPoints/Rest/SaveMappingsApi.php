<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\BodyValidator;
use MediaWiki\Rest\Validator\JsonBodyValidator;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use RequestContext;
use User;
use Wikimedia\ParamValidator\ParamValidator;

class SaveMappingsApi extends SimpleHandler {

	public function run( string $entityId ): Response {
		$presenter = WikibaseRdfExtension::getInstance()->newRestSaveMappingsPresenter( $this->getResponseFactory() );
		$useCase = WikibaseRdfExtension::getInstance()->newSaveMappingsUseCase( $presenter, $this->getUser() );
		$useCase->saveMappings( $entityId, (array)$this->getValidatedBody() );

		return $presenter->getResponse();
	}

	private function getUser(): User {
		return RequestContext::getMain()->getUser();
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

		return new JsonBodyValidator( [] );
	}

}
