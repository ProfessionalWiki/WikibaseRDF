<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use InvalidArgumentException;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class CombiningMappingPredicatesLookup implements MappingPredicatesLookup {

	/**
	 * @param array<array-key, mixed> $baseRules
	 */
	public function __construct(
		private array $baseRules,
		private WikiMappingPredicatesLookup $lookup
	) {
	}

	public function getMappingPredicates(): PredicateList {
		$predicates = $this->predicatesFromArray( $this->baseRules );
		return $predicates->plus( $this->lookup->getMappingPredicates() );
	}

	/**
	 * @param array<array-key, mixed> $predicates
	 */
	private function predicatesFromArray( array $predicates ): PredicateList {
		$predicatesList = [];
		foreach ( $predicates as $predicate ) {
			if ( !is_string( $predicate ) ) {
				continue;
			}
			try {
				$predicatesList[] = new Predicate( $predicate );
			} catch ( InvalidArgumentException ) {
			}
		}
		return new PredicateList( $predicatesList );
	}

}
