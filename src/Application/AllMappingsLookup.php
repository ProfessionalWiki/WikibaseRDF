<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

interface AllMappingsLookup {

	/**
	 * @return MappingListAndId[]
	 */
	public function getAllMappings(): array;

}
