<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

class MappingList {

	/**
	 * @var array<int, Mapping>
	 */
	private array $mappings = [];

	/**
	 * @param array<int, Mapping> $mappings
	 */
	public function __construct( array $mappings = [] ) {
		array_walk( $mappings, [ $this, 'add' ] ); // PHP 8.1: use first class callable
	}

	private function add( Mapping $mapping ): void {
		$this->mappings[] = $mapping;
	}

	/**
	 * @return array<int, Mapping>
	 */
	public function asArray(): array {
		return $this->mappings;
	}

}
