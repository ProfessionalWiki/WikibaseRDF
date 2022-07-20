<?php

declare( strict_types = 1 );

use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Repo\Rdf\EntityRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

class MappingRdfBuilder implements EntityRdfBuilder {

	public function __construct(
		private RdfWriter $writer,
		private MappingRepository $repository
	) {
	}

	public function addEntity( EntityDocument $entity ): void {
		$mappings = $this->getMappings( $entity );

		if ( $mappings === [] ) {
			return;
		}

//		$this->writer->about(
//			$this->vocabulary->entityNamespaceNames[$this->vocabulary->getEntityRepositoryName( $entity->getId() )],
//			$this->vocabulary->getEntityLName( $entity->getId() )
//		);

		foreach ( $mappings as $mapping ) {
			$this->addMapping( $mapping );
		}
	}

	/**
	 * @return array<int, Mapping>
	 */
	private function getMappings( EntityDocument $entity ): array {
		$id = $entity->getId();

		if ( $id === null ) {
			return [];
		}

		return $this->repository->getMappings( $id )->asArray();
	}

	private function addMapping( Mapping $mapping ): void {
		$this->writer
			->say( $mapping->getPredicateBase(), $mapping->getPredicateLocal() )
			->text( $mapping->object );
	}

}
