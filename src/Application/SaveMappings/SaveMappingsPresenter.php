<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\SaveMappings;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;

interface SaveMappingsPresenter {

	public function presentSuccess(): void;

	public function presentInvalidMappings( MappingList $mappings ): void;

	public function presentSaveFailed(): void;

	public function presentInvalidEntityId(): void;

}
