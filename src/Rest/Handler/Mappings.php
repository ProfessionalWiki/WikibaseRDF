<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Handler;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseException;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class Mappings extends SimpleHandler {

	private ?string $method = null;

	public static function factory(): self {
		$self = new self();

		return $self;
	}

	public function setMethodFromRequest(): void {
		$method = $this->getRequest()->getMethod();

		$this->method = strtoupper( $method );
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
		$this->setMethodFromRequest();

		if ( $this->method === "POST" ) {
			return $this->updateMapping();
		} elseif ( $this->method === "GET" ) {
			return $this->getMapping();
		}
		$response = $this->getResponseFactory()->createHttpError( 405 );
		throw new ResponseException( $response );
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
		$this->setMethodFromRequest();

		if ( $this->method === "POST" ) {
			return true;
		}
		return false;
	}
}
