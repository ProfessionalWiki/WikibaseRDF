<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Handler;

use MediaWiki\Rest\ResponseException;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\BodyValidator;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository;
use ProfessionalWiki\WikibaseRDF\Rest\Validator\Turtle;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityId;
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
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getBodyValidator( $contentType ): BodyValidator {
		return new Turtle();
	}

	/**
	 * @return array<mixed>
	 */
	public function run( string $entityId = null ): array {
		$entity = null;
		if ( $entityId ) {
			$idProc = new BasicEntityIdParser();
			$entity = $idProc->parse( $entityId );
		}
		$response = match ( $this->getMethod() ) {
			"POST" => $this->updateMapping( $entity ),
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
	 * @return null|array<int, Mapping>
	 */
	public function updateMapping( ?EntityId $entity ): ?array {
		if ( $entity === null ) {
			return null;
		}

		$mapList = $this->getValidatedBody();
		if ( !is_object( $mapList ) || ! $mapList instanceof MappingList ) {
			throw new HttpException( "Expected MappingList object!" );
		}

		$repo = new InMemoryMappingRepository();
		$repo->saveEntityMappings( $entity, $mapList );

		return $mapList->asArray();
	}

	/**
	 * @return array<mixed>
	 */
	public function getMapping(): array {
		$repo = new InMemoryMappingRepository();
		return $repo->getAllMappings();
	}

	public function needsWriteAccess(): bool {
		return $this->getMethod() === "POST";
	}
}
