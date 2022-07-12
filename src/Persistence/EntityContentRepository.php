<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use Content;
use Wikibase\DataModel\Entity\EntityId;

interface EntityContentRepository {

	public function getContent( EntityId $entityId, string $slotName ): ?Content;

	public function setContent( EntityId $entityId, string $slotName, Content $content ): void;

}
