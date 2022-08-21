<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

interface PredicatesPresenter {

	public function presentPredicates( PredicateList $localSettingsPredicates, PredicateList $wikiPredicates ): void;

}
