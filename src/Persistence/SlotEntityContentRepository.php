<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use CommentStoreComment;
use Content;
use Exception;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Storage\RevisionRecord;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Lib\Store\EntityTitleLookup;
use WikiPage;

class SlotEntityContentRepository implements EntityContentRepository {

	public function __construct(
		private Authority $authority,
		private WikiPageFactory $pageFactory,
		private EntityTitleLookup $entityTitleLookup,
		private string $slotName
	) {
	}

	public function getContent( EntityId $entityId ): ?Content {
		return $this->getWikiPage( $entityId )
			?->getRevisionRecord()
			?->getContent( $this->slotName, RevisionRecord::FOR_PUBLIC, $this->authority );
	}

	private function getWikiPage( EntityId $entityId ): ?WikiPage {
		try {
			$title = $this->entityTitleLookup->getTitleForId( $entityId );
		}
		catch ( Exception ) {
			return null;
		}

		if ( $title === null ) {
			return null;
		}

		return $this->pageFactory->newFromTitle( $title );
	}

	public function setContent( EntityId $entityId, Content $content ): void {
		$page = $this->getWikiPage( $entityId );

		if ( $page !== null ) {
			$updater = $page->newPageUpdater( $this->authority );
			$updater->setContent( $this->slotName, $content );
			$updater->saveRevision( CommentStoreComment::newUnsavedComment( 'TodoComment' ) ); // TODO
		}
	}

}
