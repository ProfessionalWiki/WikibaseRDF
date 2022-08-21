<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use CommentStoreComment;
use Content;
use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionAccessException;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Lib\Store\EntityTitleLookup;
use WikiPage;

class SlotEntityContentRepository implements EntityContentRepository {

	public function __construct(
		private Authority $authority,
		private WikiPageFactory $pageFactory,
		private EntityTitleLookup $entityTitleLookup,
		private string $slotName,
		private RevisionLookup $revisionLookup
	) {
	}

	public function getContent( EntityId $entityId, int $revisionId = 0 ): ?Content {
		$page = $this->getWikiPage( $entityId );

		if ( $page === null ) {
			return null;
		}

		$revision = $this->revisionLookup->getRevisionByTitle( $page, $revisionId );

		try {
			return $revision?->getSlot( $this->slotName, RevisionRecord::FOR_PUBLIC, $this->authority )?->getContent();
		}
		catch ( RevisionAccessException ) {
			return null;
		}
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
			$updater->saveRevision( CommentStoreComment::newUnsavedComment( 'Updated entity mappings' ) ); // TODO
		}
	}

}
