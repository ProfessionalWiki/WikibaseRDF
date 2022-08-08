<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests;

use Article;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Repo\WikibaseRepo;

class WikibaseRdfIntegrationTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'text';
		$this->tablesUsed[] = 'slots';
		$this->tablesUsed[] = 'slot_roles';
		$this->tablesUsed[] = 'page';
	}

	protected function getPageHtml( string $pageTitle ): string {
		$title = \Title::newFromText( $pageTitle );

		$article = new Article( $title, 0 );
		$article->getContext()->getOutput()->setTitle( $title );

		$article->view();

		return $article->getContext()->getOutput()->getHTML();
	}

	protected function createItemWithMappings( ItemId $itemId, MappingList $mappingList ): void {
		$this->createItem( $itemId );
		$this->setMappings( $itemId, $mappingList );
	}

	protected function createItem( ItemId $itemId ): void {
		$this->saveItem( new Item( $itemId ) );
	}

	private function saveItem( Item $item ): void {
		WikibaseRepo::getEntityStore()->saveEntity(
			$item,
			'',
			self::getTestSysop()->getUser()
		);
	}

	protected function createProperty( PropertyId $propertyId ): void {
		WikibaseRepo::getEntityStore()->saveEntity(
			new Property( $propertyId, null, 'testType' ),
			'',
			self::getTestSysop()->getUser()
		);
	}

	protected function createPropertyWithMappings( PropertyId $propertyId, MappingList $mappingList ): void {
		$this->createProperty( $propertyId );
		$this->setMappings( $propertyId, $mappingList );
	}

	protected function setMappings( EntityId $entityId, MappingList $mappingList ): void {
		$user = self::getTestSysop()->getUser();
		WikibaseRdfExtension::getInstance()->newMappingRepository( $user )->setMappings( $entityId, $mappingList );
	}

	protected function modifyItem( ItemId $itemId, string $labelText ): void {
		$item = new Item( $itemId );
		$item->setLabel( 'en', $labelText );

		$this->saveItem( $item );
	}

	/**
	 * @param string[] $predicates
	 */
	protected function setAllowedPredicates( array $predicates ): void {
		$this->setMwGlobals( 'wgWikibaseRdfPredicates', $predicates );
	}

	protected function createConfigPage( string $config ): void {
		$this->insertPage(
			'MediaWiki:' . WikibaseRdfExtension::CONFIG_PAGE_TITLE,
			$config
		);
	}

	protected function editConfigPage( string $config ): void {
		$this->editPage(
			'MediaWiki:' . WikibaseRdfExtension::CONFIG_PAGE_TITLE,
			$config
		);
	}

}
