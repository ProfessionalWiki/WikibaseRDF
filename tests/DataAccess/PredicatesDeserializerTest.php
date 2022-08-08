<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer
 */
class PredicatesDeserializerTest extends TestCase {

	private function newPredicateDeserializer(): PredicatesDeserializer {
		return new PredicatesDeserializer(
			new PredicatesTextValidator()
		);
	}

	public function testValidPredicatesAreDeserialized(): void {
		$predicates = $this->newPredicateDeserializer()->deserialize( "foo:bar\nbar:Baz" );

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' ),
			] ),
			$predicates
		);
	}

	public function testBlankLinesAreIgnored(): void {
		$predicates = $this->newPredicateDeserializer()->deserialize( "\n\nfoo:bar\n\n\nbar:Baz\n\n" );

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' ),
			] ),
			$predicates
		);
	}

	public function testWhitespaceIsIgnored(): void {
		$predicates = $this->newPredicateDeserializer()->deserialize( " foo:bar \n   \n bar:Baz  " );

		$this->assertEquals(
			new PredicateList( [
				new Predicate( 'foo:bar' ),
				new Predicate( 'bar:Baz' ),
			] ),
			$predicates
		);
	}

	public function testInvalidPredicatesResultInEmptyList(): void {
		$predicates = $this->newPredicateDeserializer()->deserialize( "notValid\nfoo:bar\nbar:Baz\nalsoNotValid" );

		$this->assertEquals(
			new PredicateList(),
			$predicates
		);
	}

}
