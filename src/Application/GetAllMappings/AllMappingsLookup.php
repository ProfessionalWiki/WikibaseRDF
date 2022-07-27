<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\GetAllMappings;

use ProfessionalWiki\WikibaseRDF\Application\EntityMappingList;

interface AllMappingsLookup {

	/**
	 * @return EntityMappingList[]
	 */
	public function getAllMappings(): array;

}
