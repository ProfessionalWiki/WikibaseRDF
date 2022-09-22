<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use InvalidArgumentException;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;

class MappingListSerializer {

	private const PREDICATE_KEY = 'predicate';
	private const OBJECT_KEY = 'object';

	public function fromJson( string $json ): MappingList {
		$array = json_decode( $json, true );

		if ( is_array( $array ) ) {
			return $this->validMappingListFromArray( $array );
		}

		return new MappingList();
	}

	/**
	 * @param array<int, array{predicate: string, object: string}> $mappings
	 */
	public function mappingListFromArray( array $mappings ): MappingList {
		return new MappingList(
			array_map(
				fn( array $mapping ) => $this->mappingFromArray( $mapping ),
				$mappings
			)
		);
	}

	/**
	 * @param array<int, array{predicate: string, object: string}> $mappings
	 */
	public function validMappingListFromArray( array $mappings ): MappingList {
		$mappingObjects = [];
		foreach ( $mappings as $mapping ) {
			try {
				$mappingObjects[] = $this->mappingFromArray( $mapping );
			} catch ( InvalidArgumentException ) {
			}
		}
		return new MappingList( $mappingObjects );
	}

	/**
	 * @param array{predicate: string, object: string} $mapping
	 */
	private function mappingFromArray( array $mapping ): Mapping {
		return new Mapping(
			predicate: $mapping[self::PREDICATE_KEY],
			object: $mapping[self::OBJECT_KEY]
		);
	}

	public function toJson( MappingList $mappingList ): string {
		return (string)json_encode( $this->mappingListToArray( $mappingList ) );
	}

	/**
	 * @return array<int, array{predicate: string, object: string}>
	 */
	public function mappingListToArray( MappingList $mappings ): array {
		return array_map(
			fn( Mapping $mapping ) => [
				self::PREDICATE_KEY => $mapping->predicate,
				self::OBJECT_KEY => $mapping->object
			],
			$mappings->asArray()
		);
	}

}
