<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\SaveMappings;

interface SaveMappingsPresenter {

	public function presentSuccess(): void;

	/**
	 * @param array<int, array{predicate: string, object: string}> $mappings
	 */
	public function presentInvalidMappings( array $mappings ): void;

	public function presentSaveFailed(): void;

	public function presentInvalidEntityId(): void;

	public function presentPermissionDenied(): void;

}
