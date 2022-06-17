<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Handler;

use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\Validator\BodyValidator;
use MediaWiki\Rest\HttpException;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use MediaWiki\Rest\Validator\JsonBodyValidator;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityId;
use Wikimedia\ParamValidator\ParamValidator;

class UpdateMapping extends SimpleHandler {

	public function __construct( protected MappingRepository $mappingRepository ) {
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
	 * @inheritDoc
	 */
	public function getBodyValidator( $contentType ): BodyValidator {
		if ( $contentType !== 'application/json' ) {
			throw new HttpException( "Unsupported Content-Type",
				415,
				[ 'content_type' => $contentType ]
			);
		}

		return new JsonBodyValidator( [
			'mapping' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => true,
			]
		] );
	}

	/**
	 * @return array<mixed>
	 */
	public function run( string $entityId ): array {
		$idProc = new BasicEntityIdParser();
		$entity = $idProc->parse( $entityId );
		if ( $entity ) {
			$body = $this->getValidatedBody();
			$arr = [];
			foreach ( $body['mapping'] as $map ) {
				$arr[] = new Mapping( predicate: $map['predicate'], object: $map['object'] );
			}
			$this->mappingRepository->saveEntityMappings( $entity, new MappingList( $arr ) );
		}
		return [];
	}

	/**
	 * @return null|array<int, Mapping>
	 */
	public function updateMapping( ?EntityId $entity ): ?array {
		if ( $entity === null ) {
			return null;
		}

		$mapList = $this->getValidatedBody();
		if ( !is_object( $mapList ) || !$mapList instanceof MappingList ) {
			throw new HttpException( "Expected MappingList object!" );
		}

		$repo = new InMemoryMappingRepository();
		$repo->saveEntityMappings( $entity, $mapList );

		return $mapList->asArray();
	}

	public function needsWriteAccess(): bool {
		return true;
	}
}
