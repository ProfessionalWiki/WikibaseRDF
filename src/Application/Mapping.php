<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use InvalidArgumentException;

class Mapping {

	public function __construct(
		public /* readonly */ string $predicate,
		public /* readonly */ string $object,
	) {
		if ( !str_contains( $this->predicate, ':' ) ) {
			throw new InvalidArgumentException( 'Invalid predicate' );
		}

		if ( !str_contains( $this->object, '://' ) ) {
			throw new InvalidArgumentException( 'Invalid object' );
		}
	}

	public function getPredicateBase(): string {
		return explode( ':', $this->predicate, 2 )[0];
	}

	public function getPredicateLocal(): string {
		return explode( ':', $this->predicate, 2 )[1];
	}

}
