<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

interface MappingPredicatesLookup {

	public function getMappingPredicates(): PredicateList;

}
