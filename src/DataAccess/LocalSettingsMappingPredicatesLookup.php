<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use InvalidArgumentException;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class LocalSettingsMappingPredicatesLookup implements MappingPredicatesLookup {

	/**
	 * @param array<array-key, mixed> $predicates
	 */
	public function __construct(
		private array $predicates
	) {
	}

	public function getMappingPredicates(): PredicateList {
		$predicatesList = [];
		foreach ( $this->predicates as $predicate ) {
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
