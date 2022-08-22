<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

class PredicateList {

	/**
	 * @var Predicate[]
	 */
	private array $predicates = [];

	/**
	 * @param Predicate[] $predicates
	 */
	public function __construct(
		array $predicates = []
	) {
		array_walk( $predicates, [ $this, 'addPredicate' ] ); // PHP 8.1: use first class callable
	}

	private function addPredicate( Predicate $predicate ): void {
		if ( !in_array( $predicate, $this->predicates ) ) {
			$this->predicates[] = $predicate;
		}
	}

	public function plus( self $predicates ): self {
		return new self( array_merge( $this->predicates, $predicates->predicates ) );
	}

	public function contains( string $predicate ): bool {
		return in_array( new Predicate( $predicate ), $this->predicates );
	}

	/**
	 * @return Predicate[]
	 */
	public function asArray(): array {
		return $this->predicates;
	}

}
