<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Persistence;

use CommentStoreComment;
use Content;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\Authority;
use MediaWiki\Storage\RevisionRecord;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Repo\WikibaseRepo;
use WikiPage;

class WikiPageEntityContentRepository implements EntityContentRepository {

	public function __construct(
		private Authority $authority
	) {
	}

	public function getContent( EntityId $entityId, string $slotName ): ?Content {
		return $this->getWikiPage( $entityId )
			?->getRevisionRecord()
			?->getContent( $slotName, RevisionRecord::FOR_PUBLIC, $this->authority );
	}

	private function getWikiPage( EntityId $entityId ): ?WikiPage {
		// TODO: inject
		return MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle(
			WikibaseRepo::getEntityTitleLookup()->getTitleForId( $entityId )
		);
	}

	public function setContent( EntityId $entityId, string $slotName, Content $content ): void {
		$page = $this->getWikiPage( $entityId );

		if ( $page !== null ) {
			$updater = $page->newPageUpdater( $this->authority );
			$updater->setContent( $slotName, $content );
			$updater->saveRevision( CommentStoreComment::newUnsavedComment( 'TodoComment' ) ); // TODO
		}
	}

}
