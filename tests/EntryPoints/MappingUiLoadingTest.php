<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests;

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

		// Causes error in Wikibase Repo hook
//		Article::newFromTitle( \Title::newFromText( 'Item:Q90019001' ), \RequestContext::getMain() )->view();

		// TODO: find a way to get at the rendered HTML, so we can assert the right strings are there
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

	// TODO: test UI is NOT present for Q404

	// TODO: test UI is not present for existing NormalPage

}
