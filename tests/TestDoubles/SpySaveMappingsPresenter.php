<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\TestDoubles;

use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;

class SpySaveMappingsPresenter implements SaveMappingsPresenter {

	public bool $showedSuccess = false;
	public ?MappingList $invalidMappings = null;
	public bool $showedSaveFailed = false;
	public bool $showedInvalidEntityId = false;

	public function presentSuccess(): void {
		$this->showedSuccess = true;
	}

	public function presentInvalidMappings( MappingList $mappings ): void {
		$this->invalidMappings = $mappings;
	}

	public function presentSaveFailed(): void {
		$this->showedSaveFailed = true;
	}

	public function presentInvalidEntityId(): void {
		$this->showedInvalidEntityId = true;
	}

}
