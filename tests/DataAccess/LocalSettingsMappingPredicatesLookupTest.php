<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\DataAccess\LocalSettingsMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\DataAccess\LocalSettingsMappingPredicatesLookup
 */
class LocalSettingsMappingPredicatesLookupTest extends WikibaseRdfIntegrationTest {

	public function testEmptyConfigReturnsEmptyList(): void {
		$mappings = [];
		$lookup = new LocalSettingsMappingPredicatesLookup( $mappings );

		$this->assertEquals(
			new PredicateList(),
			$lookup->getMappingPredicates()
		);
	}

	public function testValidPredicatesAreInList(): void {
		$mappings = [ 'foo:bar', 'bar:baz' ];
		$lookup = new LocalSettingsMappingPredicatesLookup( $mappings );

		$this->assertEquals(
			new PredicateList( [ new Predicate( 'foo:bar' ), new Predicate( 'bar:baz' ) ] ),
			$lookup->getMappingPredicates()
		);
	}

	public function testInvalidPredicatesAreNotInList(): void {
		$mappings = [ 'foo:bar', 'not valid', 'bar:baz' ];
		$lookup = new LocalSettingsMappingPredicatesLookup( $mappings );

		$this->assertEquals(
			new PredicateList( [ new Predicate( 'foo:bar' ), new Predicate( 'bar:baz' ) ] ),
			$lookup->getMappingPredicates()
		);
	}

}
