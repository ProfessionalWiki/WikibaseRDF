<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application\GetAllMappings;

use ProfessionalWiki\WikibaseRDF\Application\EntityMappingList;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\IResultWrapper;

class SqlAllMappingsLookup implements AllMappingsLookup {

	public function __construct(
		private DBConnRef $database,
		private string $slotName,
		private EntityIdParser $entityIdParser,
		private MappingListSerializer $serializer
	) {
	}

	public function getAllMappings(): array {
		return $this->resultsToEntityMappingList( $this->fetchResults() );
	}

	private function fetchResults(): IResultWrapper {
		return $this->database->newSelectQueryBuilder()
			->select( [
				'text.old_text',
				'page.page_title',
			] )
			->from( 'text' )
			->join( 'slots', conds: 'slots.slot_content_id=text.old_id' )
			->join( 'slot_roles', conds: 'slot_roles.role_id=slots.slot_role_id' )
			->join( 'page', conds: 'page.page_latest=slots.slot_revision_id' )
			->where( [ 'slot_roles.role_name' => $this->slotName ] )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	/**
	 * @return EntityMappingList[]
	 */
	private function resultsToEntityMappingList( IResultWrapper $results ): array {
		$entityMappingsList = [];
		foreach ( $results as $row ) {
			if ( !is_object( $row ) ) {
				continue;
			}
			$entityMappingsList[] = new EntityMappingList(
				$this->entityIdParser->parse( (string)$row->page_title ),
				$this->serializer->fromJson( (string)$row->old_text )
			);
		}
		return $entityMappingsList;
	}

}
