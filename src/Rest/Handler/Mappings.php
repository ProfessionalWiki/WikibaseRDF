<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Handler;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseException;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class Mappings extends SimpleHandler {

	public static function factory(): self {
		$self = new self();

		return $self;
	}

	public function getMethod(): string {
		return strtoupper( $this->getRequest()->getMethod() );
	}

	/**
	 * @return array<string, array<string, string|bool>>
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
	 * @return array<mixed>
	 */
	public function run( string $entityId = null ): array {
		$response = match ( $this->getMethod() ) {
			"POST" => $this->updateMapping(),
			"GET" => $this->getMapping(),
			default => null
		};
		// Methods not mentioned in extension.json are rejected earlier when running the code, but
		// under phpunit, they are not.
		if ( $response === null ) {
			throw new ResponseException( $this->getResponseFactory()->createHttpError( 405 ) );
		}
		return $response;
	}

	/**
	 * @return array<mixed>
	 */
	public function updateMapping(): array {
		return [];
	}

	/**
	 * @return array<mixed>
	 */
	public function getMapping(): array {
		return [];
	}

	public function needsWriteAccess(): bool {
		if ( $this->getMethod() === "POST" ) {
			return true;
		}
		return false;
	}
}
