<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\SaveMappings;

use PermissionsError;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Throwable;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;

class SaveMappingsUseCase {

	private const PREDICATE_KEY = 'predicate';
	private const OBJECT_KEY = 'object';

	/**
	 * @param string[] $allowedPredicates
	 */
	public function __construct(
		private SaveMappingsPresenter $presenter,
		private MappingRepository $repository,
		private array $allowedPredicates,
		private EntityIdParser $entityIdParser,
		private MappingListSerializer $mappingListSerializer
	) {
	}

	/**
	 * @param array<mixed> $mappingsRequest
	 */
	public function saveMappings( string $entityIdValue, array $mappingsRequest ): void {
		try {
			$entityId = $this->entityIdParser->parse( $entityIdValue );
		} catch ( EntityIdParsingException ) {
			$this->presenter->presentInvalidEntityId();
			return;
		}

		if ( !$this->mappingsIsList( $mappingsRequest ) ) {
			$this->presenter->presentInvalidMappings( [] );
			return;
		}

		$normalizedMappings = $this->normalizeMappings( $mappingsRequest );

		$invalidMappings = $this->getInvalidMappings( $normalizedMappings );
		if ( $invalidMappings !== [] ) {
			$this->presenter->presentInvalidMappings( $invalidMappings );
			return;
		}

		$mappings = $this->mappingListSerializer->mappingListFromArray( $normalizedMappings );

		try {
			$this->repository->setMappings( $entityId, $mappings );
			$this->presenter->presentSuccess();
		} catch ( PermissionsError $exception ) {
			$this->presenter->presentPermissionDenied( $exception );
		} catch ( Throwable ) {
			$this->presenter->presentSaveFailed();
		}
	}

	/**
	 * @param array<mixed> $mappings
	 */
	private function mappingsIsList( array $mappings ): bool {
		return count( array_filter( array_keys( $mappings ), 'is_string' ) ) === 0;
	}

	/**
	 * @param array<mixed> $mappings
	 *
	 * @return array<int, array{predicate: string, object: string}>
	 */
	private function normalizeMappings( array $mappings ): array {
		$normalized = [];
		foreach ( $mappings as $mapping ) {
			if ( !is_array( $mapping ) ) {
				$normalized[] = [ 'predicate' => '', 'object' => '' ];
				continue;
			}
			$normalized[] = [
				self::PREDICATE_KEY  => (string)( $mapping[self::PREDICATE_KEY] ?? '' ),
				self::OBJECT_KEY => (string)( $mapping[self::OBJECT_KEY] ?? '' ),
			];
		}
		return $normalized;
	}

	/**
	 * @param array<int, array{predicate: string, object: string}> $mappings
	 *
	 * @return array<int, array{predicate: string, object: string}>
	 */
	private function getInvalidMappings( array $mappings ): array {
		// TOOD: we might want to keep the original index to quickly identify the row when getting this in the UI JS.
		return array_values(
			array_filter(
				$mappings,
				fn( array $mapping ) => !$this->mappingIsValid( $mapping[self::PREDICATE_KEY], $mapping[self::OBJECT_KEY] )
			)
		);
	}

	private function mappingIsValid( string $predicate, string $object ): bool {
		return $predicate !== ''
			&& $object !== ''
			&& str_contains( $predicate, ':' )
			&& in_array( $predicate, $this->allowedPredicates );
	}

}
