<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

class PredicatesTextValidator {

	/**
	 * @var string[]
	 */
	private array $validPredicates = [];

	/**
	 * @var string[]
	 */
	private array $invalidPredicates = [];

	public function validate( string $predicatesText ): bool {
		$lines = $this->normalizeLines( $predicatesText );

		foreach ( $lines as $line ) {
			if ( $this->predicateIsValid( $line ) ) {
				$this->validPredicates[] = $line;
			} else {
				$this->invalidPredicates[] = $line;
			}
		}

		return $this->invalidPredicates === [];
	}

	/**
	 * @return string[]
	 */
	private function normalizeLines( string $predicatesText ): array {
		return array_filter(
			array_map(
				fn( string $predicate ) => trim( $predicate ),
				explode( "\n", $predicatesText )
			)
		);
	}

	private function predicateIsValid( string $predicate ): bool {
		return str_contains( $predicate, ':' );
	}

	/**
	 * @return string[]
	 */
	public function getValidPredicates(): array {
		return $this->validPredicates;
	}

	/**
	 * @return string[]
	 */
	public function getInvalidPredicates(): array {
		return $this->invalidPredicates;
	}

}
