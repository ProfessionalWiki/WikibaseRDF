<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\TestDoubles;

use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;
use Throwable;

class SpySaveMappingsPresenter implements SaveMappingsPresenter {

	public bool $showedSuccess = false;
	/** @var array<int, array{predicate: string, object: string}> */
	public array $invalidMappings = [];
	public bool $showedSaveFailed = false;
	public bool $showedInvalidEntityId = false;
	public bool $showedPermissionDenied = false;

	public function presentSuccess(): void {
		$this->showedSuccess = true;
	}

	/**
	 * @param array<int, array{predicate: string, object: string}> $mappings
	 */
	public function presentInvalidMappings( array $mappings ): void {
		$this->invalidMappings = $mappings;
	}

	public function presentSaveFailed(): void {
		$this->showedSaveFailed = true;
	}

	public function presentInvalidEntityId(): void {
		$this->showedInvalidEntityId = true;
	}

	public function presentPermissionDenied(): void {
		$this->showedPermissionDenied = true;
	}

}
