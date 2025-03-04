<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\User\User;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikimedia\ParamValidator\ParamValidator;

class SaveMappingsApi extends SimpleHandler {

	public function run( string $entityId ): Response {
		$presenter = WikibaseRdfExtension::getInstance()->newRestSaveMappingsPresenter( $this->getResponseFactory() );
		$useCase = WikibaseRdfExtension::getInstance()->newSaveMappingsUseCase( $presenter, $this->getUser() );

		$body = is_array( $this->getValidatedBody() ) ? $this->getValidatedBody() : [];
		$mappings = isset( $body['mappings'] ) ? (array)$body['mappings'] : [];

		$useCase->saveMappings( $entityId, $mappings );

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
	 * @return array<string, array<string, mixed>>
	 */
	public function getBodyParamSettings(): array {
		return [
			'mappings' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => true
			],
		];
	}

}
