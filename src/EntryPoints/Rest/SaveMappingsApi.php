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

}
