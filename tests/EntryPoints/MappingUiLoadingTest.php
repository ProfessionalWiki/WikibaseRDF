<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests;

use Article;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\MediaWikiHooks
 * @group Database
 */
class MappingUiLoadingTest extends \MediaWikiIntegrationTestCase {

	public function testExistingItemHasEditingUiWithItsMappings(): void {
		$this->createItemWithMappings(
			new ItemId( 'Q90019001' ),
			new MappingList( [
				new Mapping( 'wiki:hosting', 'https://pro.wiki' )
			] )
		);

		$html = $this->getPageHtml( 'Item:Q90019001' );
		$this->assertStringContainsString( 'wikibase-rdf', $html );
		$this->assertStringContainsString( 'wiki:hosting', $html );
		$this->assertStringContainsString( 'https://pro.wiki', $html );
	}

	private function getPageHtml( string $pageTitle ): string {
		$title = \Title::newFromText( $pageTitle );

		$article = new Article( $title, 0 );
		$article->getContext()->getOutput()->setTitle( $title );

		$article->view();

		return $article->getContext()->getOutput()->getHTML();
	}

	private function createItemWithMappings( ItemId $itemId, MappingList $mappingList ): void {
		$user = self::getTestSysop()->getUser();

		WikibaseRepo::getEntityStore()->saveEntity(
			new Item( $itemId ),
			'',
			$user
		);

		WikibaseRdfExtension::getInstance()->newMappingRepository( $user )->setMappings( $itemId, $mappingList );
	}

	public function test404ItemHasNoEditingUI(): void {
		$html = $this->getPageHtml( 'Item:Q404404404' );
		$this->assertStringNotContainsString( 'wikibase-rdf', $html );
	}

	public function testExistingNonItemHasNoEditingUi(): void {
		$this->editPage( 'MappingUiLoadingTest', 'Pink Fluffy Unicorns Dancing On Rainbows' );
		$html = $this->getPageHtml( 'MappingUiLoadingTest' );
		$this->assertStringContainsString( 'Pink Fluffy Unicorns Dancing On Rainbows', $html );
		$this->assertStringNotContainsString( 'wikibase-rdf', $html );
	}

}
