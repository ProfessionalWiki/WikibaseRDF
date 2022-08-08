<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Tests\WikibaseRdfIntegrationTest;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\DataAccess\CombiningMappingPredicatesLookup
 */
class CombiningMappingPredicatesLookupTest extends WikibaseRdfIntegrationTest {

	public function testEmptyLocalSettingsAndEmptyPageConfig(): void {
		$this->setAllowedPredicates( [] );
		$this->createConfigPage( '' );

		$lookup = WikibaseRdfExtension::getInstance()->newMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList(),
			$lookup->getMappingPredicates()
		);
	}

	public function testEmptyLocalSettingsAndValidPageConfigGetsCombined(): void {
		$this->setAllowedPredicates( [] );
		$this->createConfigPage( "foo:bar\nbar:Baz" );

		$lookup = WikibaseRdfExtension::getInstance()->newMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' )
			] ),
			$lookup->getMappingPredicates()
		);
	}

	public function testValidLocalSettingsAndEmptyPageConfigGetsCombined(): void {
		$this->setAllowedPredicates( [ 'owl:sameAs','owl:SymmetricProperty' ] );
		$this->createConfigPage( '' );

		$lookup = WikibaseRdfExtension::getInstance()->newMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'owl:sameAs' ),
				new Predicate( 'owl:SymmetricProperty' ),
			] ),
			$lookup->getMappingPredicates()
		);
	}

	public function testInvalidLocalSettingsAreIgnored(): void {
		$this->setAllowedPredicates( [ 'owl:sameAs','fooBar' ] );
		$this->createConfigPage( '' );

		$lookup = WikibaseRdfExtension::getInstance()->newMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'owl:sameAs' ),
			] ),
			$lookup->getMappingPredicates()
		);
	}

	public function testValidLocalSettingsAndValidPageConfigGetsCombined(): void {
		$this->setAllowedPredicates( [ 'owl:sameAs','owl:SymmetricProperty' ] );
		$this->createConfigPage( "foo:bar\nbar:Baz" );

		$lookup = WikibaseRdfExtension::getInstance()->newMappingPredicatesLookup();

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'owl:sameAs' ),
				new Predicate( 'owl:SymmetricProperty' ),
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' ),
			] ),
			$lookup->getMappingPredicates()
		);
	}

}
