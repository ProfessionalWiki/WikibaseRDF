<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;
use ProfessionalWiki\WikibaseRDF\DataAccess\WikiMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\PageContentFetcher;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\DataAccess\WikiMappingPredicatesLookup
 */
class WikiMappingPredicatesLookupTest extends WikibaseRdfIntegrationTest {

	private function newWikiMappingPredicatesLookup(): WikiMappingPredicatesLookup {
		return new WikiMappingPredicatesLookup(
			new PageContentFetcher(
				MediaWikiServices::getInstance()->getTitleParser(),
				MediaWikiServices::getInstance()->getRevisionLookup()
			),
			new PredicatesDeserializer(
				new PredicatesTextValidator()
			),
			WikibaseRdfExtension::CONFIG_PAGE_TITLE
		);
	}

	public function testEmptyConfigResultsInEmptyList(): void {
		$this->createConfigPage( '' );

		$lookup = $this->newWikiMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList(),
			$lookup->getMappingPredicates()
		);
	}

	public function testValidConfigIsRetrieved(): void {
		$this->createConfigPage( "foo:bar\nbar:Baz" );

		$lookup = $this->newWikiMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' )
			] ),
			$lookup->getMappingPredicates()
		);
	}

}
