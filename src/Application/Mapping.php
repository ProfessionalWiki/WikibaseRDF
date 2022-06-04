<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

class Mapping {

	public function __construct(
		public /* readonly */ string $predicate,
		public /* readonly */ string $object,
	) {
	}

}
