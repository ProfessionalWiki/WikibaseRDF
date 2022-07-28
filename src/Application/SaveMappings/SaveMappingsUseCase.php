<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\SaveMappings;

use InvalidArgumentException;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Throwable;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;

class SaveMappingsUseCase {

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

	public function saveMappings( string $entityIdValue, string $mappingsJson ): void {
		try {
			$entityId = $this->entityIdParser->parse( $entityIdValue );
		} catch ( EntityIdParsingException ) {
			$this->presenter->presentInvalidEntityId();
			return;
		}

		try {
			$mappings = $this->mappingListSerializer->fromJson( $mappingsJson );
		} catch ( InvalidArgumentException ) {
			// TODO: get the actual invalid items
			$this->presenter->presentInvalidMappings( new MappingList() );
			return;
		}

		// TODO: "invalid" is too generic here. We have malformed predicates, disallowed predicates and invalid objects.
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
