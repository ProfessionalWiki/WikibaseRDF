<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use InvalidArgumentException;

class Predicate {

	public function __construct(
		public /* readonly */ string $predicate
	) {
		if ( !str_contains( $this->predicate, ':' ) ) {
			throw new InvalidArgumentException( 'Invalid predicate' );
		}
	}

}
