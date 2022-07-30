<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use Wikibase\DataModel\Entity\EntityId;

class MappingListAndId {

	public function __construct(
		public /* readonly */  EntityId $entityId,
		public /* readonly */  MappingList $mappingList
	) {
	}

}
