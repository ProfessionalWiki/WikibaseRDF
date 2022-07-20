<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use Wikibase\DataModel\Entity\EntityId;

/**
 * TODO: Dummy repo for testing - to be deleted
 */
class StubMappingRepository implements MappingRepository {

	public function getMappings( EntityId $entityId ): MappingList {
		return new MappingList( [
			new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' ),
			new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' ),
			new Mapping( 'rdfs:subClassOf', 'http://www.example.com/bar' ),
			new Mapping( 'rdfs:subPropertyOf', 'http://www.example.com/baz' ),
		] );
	}

	public function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		// TODO: not needed for stub UI
	}

}
