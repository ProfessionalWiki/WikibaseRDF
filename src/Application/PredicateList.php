<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

class PredicateList {

	/**
	 * @param Predicate[] $predicates
	 */
	public function __construct(
		private array $predicates = []
	) {
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
