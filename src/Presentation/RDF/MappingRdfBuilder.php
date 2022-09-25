<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation\RDF;

use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Repo\Rdf\EntityRdfBuilder;
use Wikimedia\Purtle\RdfWriter;

class MappingRdfBuilder implements EntityRdfBuilder {

	public function __construct(
		private RdfWriter $writer,
		private MappingRepository $repository,
		private PropertyMappingPrefixBuilder $propertyMappingPrefixBuilder
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

		if ( $entity->getType() === Property::ENTITY_TYPE ) {
			$this->addPropertyMappings( $mappings, $entity );
		} else {
			$this->addItemMappings( $mappings );
		}
	}

	/**
	 * @param Mapping[] $mappings
	 */
	private function addItemMappings( array $mappings ): void {
		foreach ( $mappings as $mapping ) {
			$this->addItemMapping( $mapping );
		}
	}

	/**
	 * @param Mapping[] $mappings
	 */
	private function addPropertyMappings( array $mappings, EntityDocument $entity ): void {
		$id = $entity->getId();

		if ( $id === null ) {
			return;
		}

		foreach ( $mappings as $mapping ) {
			$this->addPropertyMapping( $mapping, $id );
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

	private function addItemMapping( Mapping $mapping ): void {
		$this->writer
			->say( $mapping->getPredicateBase(), $mapping->getPredicateLocal() )
			->is( $mapping->object );
	}

	private function addPropertyMapping( Mapping $mapping, EntityId $entityId ): void {
		$this->writer
			->about( $this->propertyMappingPrefixBuilder->getPrefix(), $entityId->getLocalPart() )
			->a( 'owl', 'ObjectProperty' )
			->say( $mapping->getPredicateBase(), $mapping->getPredicateLocal() )
			->is( $mapping->object );
	}

}
