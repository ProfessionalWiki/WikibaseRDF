<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use Content;
use Exception;
use Wikibase\DataModel\Entity\EntityId;

/**
 * Repository for Content attached to an Entity. NOT for EntityContent.
 */
interface EntityContentRepository {

	public function getContent( EntityId $entityId, int $revisionId = 0 ): ?Content;

	/**
	 * @throws Exception
	 */
	public function setContent( EntityId $entityId, Content $content ): void;

}
