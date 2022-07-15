<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use Content;
use SplObjectStorage;
use Wikibase\DataModel\Entity\EntityId;

class InMemoryEntityContentRepository implements EntityContentRepository {

	private SplObjectStorage $contentList;

	public function __construct() {
		$this->contentList = new SplObjectStorage();
	}

	public function getContent( EntityId $entityId ): ?Content {
		return $this->contentList->offsetExists( $entityId ) ? $this->contentList->offsetGet( $entityId ) : null;
	}

	public function setContent( EntityId $entityId, Content $content ): void {
		$this->contentList->offsetSet( $entityId, $content );
	}

}
