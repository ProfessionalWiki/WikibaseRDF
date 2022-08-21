<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use InvalidArgumentException;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class CombiningMappingPredicatesLookup implements MappingPredicatesLookup {

	public function __construct(
		private LocalSettingsMappingPredicatesLookup $localSettingLookup,
		private WikiMappingPredicatesLookup $wikiLookup
	) {
	}

	public function getMappingPredicates(): PredicateList {
		return $this->localSettingLookup->getMappingPredicates()->plus( $this->wikiLookup->getMappingPredicates() );
	}

}
