<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class PredicatesDeserializer {

	public function __construct(
		private PredicatesTextValidator $validator
	) {
	}

	public function deserialize( string $predicatesText ): PredicateList {
		if ( !$this->validator->validate( $predicatesText ) ) {
			return new PredicateList();
		}

		return new PredicateList(
			array_values(
				array_map(
					fn( string $predicate ) => new Predicate( $predicate ),
					$this->validator->getValidPredicates()
				)
			)
		);
	}

}
