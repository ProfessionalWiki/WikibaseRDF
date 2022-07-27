<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests;

use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\EntryPoints\MediaWikiHooks
 * @group Database
 */
class MappingUiLoadingTest extends WikibaseRdfIntegrationTest {

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
