<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\SaveMappings;

use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Throwable;
use Wikibase\DataModel\Entity\EntityId;

class SaveMappingsUseCase {

	/**
	 * @param string[] $allowedPredicates
	 */
	public function __construct(
		private SaveMappingsPresenter $presenter,
		private MappingRepository $repository,
		private array $allowedPredicates
	) {
	}

	public function saveMappings( EntityId $entityId, MappingList $mappings ): void {
		$invalidMappings = $this->getInvalidMappings( $mappings );
		if ( $invalidMappings->asArray() !== [] ) {
			$this->presenter->presentInvalidMappings( $invalidMappings );
			return;
		}

		try {
			$this->repository->setMappings( $entityId, $mappings );
			$this->presenter->presentSuccess();
		} catch ( Throwable ) {
			$this->presenter->presentSaveFailed();
		}
	}

	private function getInvalidMappings( MappingList $mappings ): MappingList {
		return new MappingList(
			array_filter(
				$mappings->asArray(),
				fn( Mapping $mapping ) => !in_array( $mapping->predicate, $this->allowedPredicates )
			)
		);
	}

}
