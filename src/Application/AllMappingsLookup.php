<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

interface AllMappingsLookup {

	/**
	 * @return EntityMappingList[]
	 */
	public function getAllMappings(): array;

}
