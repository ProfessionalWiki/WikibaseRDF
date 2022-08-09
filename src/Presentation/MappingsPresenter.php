<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;

interface MappingsPresenter {

	public function showMappings( MappingList $mappingList, bool $canEdit ): void;

}
