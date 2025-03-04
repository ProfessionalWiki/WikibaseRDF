<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use ProfessionalWiki\WikibaseRDF\Application\AllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Application\MappingListAndId;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IResultWrapper;

class SqlAllMappingsLookup implements AllMappingsLookup {

	public function __construct(
		private IDatabase $database,
		private string $slotName,
		private EntityIdParser $entityIdParser,
		private MappingListSerializer $serializer
	) {
	}

	/**
	 * @return MappingListAndId[]
	 */
	public function getAllMappings(): array {
		return $this->resultsToEntityMappingList( $this->fetchResults() );
	}

	private function fetchResults(): IResultWrapper {
		return $this->database->newSelectQueryBuilder()
			->select( [
				't.old_text',
				'p.page_title',
			] )
			->from( 'text', 't' )
			->join( 'slots', 's', 's.slot_content_id=t.old_id' )
			->join( 'slot_roles', 'r', 'r.role_id=s.slot_role_id' )
			->join( 'page', 'p', 'p.page_latest=s.slot_revision_id' )
			->where( [ 'r.role_name' => $this->slotName ] )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	/**
	 * @return MappingListAndId[]
	 */
	private function resultsToEntityMappingList( IResultWrapper $results ): array {
		$entityMappingsList = [];
		foreach ( $results as $row ) {
			if ( !is_object( $row ) ) {
				continue;
			}
			$entityMappingsList[] = new MappingListAndId(
				$this->entityIdParser->parse( (string)$row->page_title ),
				$this->serializer->fromJson( (string)$row->old_text )
			);
		}
		return $entityMappingsList;
	}

}
