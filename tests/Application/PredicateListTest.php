<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\PredicateList
 */
class PredicateListTest extends TestCase {

	public function testCanConstructEmptyList(): void {
		$list = new PredicateList();

		$this->assertSame( [], $list->asArray() );
	}

	public function testCanAccessPredicatesAsArray(): void {
		$predicatesArray = [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'rdfs:subClassOf' )
		];

		$list = new PredicateList( $predicatesArray );

		$this->assertEquals( $predicatesArray, $list->asArray() );
	}

	public function testCanCombineLists(): void {
		$predicatesArray1 = [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'rdfs:subClassOf' )
		];

		$list1 = new PredicateList( $predicatesArray1 );

		$predicatesArray2 = [
			new Predicate( 'foo:bar' ),
			new Predicate( 'bar:baz' )
		];

		$list2 = new PredicateList( $predicatesArray2 );

		$allList = new PredicateList( array_merge( $predicatesArray1, $predicatesArray2 ) );

		$this->assertEquals( $allList, $list1->plus( $list2 ) );
	}

	public function testContainsPredicate(): void {
		$list = new PredicateList( [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'rdfs:subClassOf' )
		] );

		$this->assertTrue( $list->contains( 'rdfs:subClassOf' ) );
	}

	public function testDoesNotContainPredicate(): void {
		$list = new PredicateList( [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'rdfs:subClassOf' )
		] );

		$this->assertFalse( $list->contains( 'foo:bar' ) );
	}

	public function testFiltersDuplicates(): void {
		$list = new PredicateList( [
			new Predicate( 'foo:bar' ),
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'rdfs:subClassOf' ),
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'owl:sameAs' ),
		] );

		$this->assertEquals(
			[
				new Predicate( 'foo:bar' ),
				new Predicate( 'owl:sameAs' ),
				new Predicate( 'rdfs:subClassOf' ),
			],
			$list->asArray()
		);
	}

}
