<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Handler;

use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;

class GetMappings extends SimpleHandler {

	public function __construct( protected MappingRepository $mappingRepository ) {
	}

	/**
	 * @return array<mixed>
	 */
	public function getParamSettings(): array {
		return [];
	}

	/**
	 * @return array<mixed>
	 */
	public function run(): array {
		return $this->mappingRepository->getAllMappings();
	}

	public function needsWriteAccess(): bool {
		return false;
	}
}
