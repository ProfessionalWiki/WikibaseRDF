<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation\RDF;

use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Repo\Rdf\EntityRdfBuilder;

class MultiEntityRdfBuilder implements EntityRdfBuilder {

	/**
	 * @var EntityRdfBuilder[]
	 */
	private array $builders;

	public function __construct(
		EntityRdfBuilder ...$builders
	) {
		$this->builders = $builders;
	}

	public function addEntity( EntityDocument $entity ): void {
		foreach ( $this->builders as $builder ) {
			$builder->addEntity( $entity );
		}
	}

}
