<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use Content;
use Wikibase\DataModel\Entity\EntityId;

class InMemoryEntityContentRepository implements EntityContentRepository {

	/**
	 * @var array<string, Content>
	 */
	private array $contentList = [];

	public function getContent( EntityId $entityId, int $revisionId = 0 ): ?Content {
		return $this->contentList[$entityId->getSerialization()] ?? null;
	}

	public function setContent( EntityId $entityId, Content $content ): void {
		$this->contentList[$entityId->getSerialization()] = $content;
	}

}
