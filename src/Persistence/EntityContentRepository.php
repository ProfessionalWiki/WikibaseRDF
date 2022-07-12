<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use Content;
use Wikibase\DataModel\Entity\EntityId;

/**
 * Repository for Content attached to an Entity. NOT for EntityContent.
 */
interface EntityContentRepository {

	public function getContent( EntityId $entityId ): ?Content;

	public function setContent( EntityId $entityId, Content $content ): void;

}
