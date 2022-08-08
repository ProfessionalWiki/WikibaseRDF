<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator
 */
class PredicatesTextValidatorTest extends TestCase {

	public function testValidPredicatesAreValid(): void {
		$validator = new PredicatesTextValidator();

		$this->assertTrue( $validator->validate( "foo:bar\nbar:Baz" ) );

		$this->assertSame(
			[ 'foo:bar', 'bar:Baz' ],
			$validator->getValidPredicates()
		);
	}

	public function testBlankLinesAreIgnored(): void {
		$validator = new PredicatesTextValidator();

		$this->assertTrue( $validator->validate( "\n\nfoo:bar\n\n\nbar:Baz\n\n" ) );

		$this->assertSame(
			[ 'foo:bar', 'bar:Baz' ],
			$validator->getValidPredicates()
		);
	}

	public function testWhitespaceIsIgnored(): void {
		$validator = new PredicatesTextValidator();

		$this->assertTrue( $validator->validate( " foo:bar \n   \n bar:Baz  " ) );

		$this->assertSame(
			[ 'foo:bar', 'bar:Baz' ],
			$validator->getValidPredicates()
		);
	}

	public function testInvalidPredicatesResultInEmptyList(): void {
		$validator = new PredicatesTextValidator();

		$this->assertFalse( $validator->validate( "notValid\nfoo:bar\nbar:Baz\nalsoNotValid" ) );

		$this->assertSame(
			[ 'notValid', 'alsoNotValid' ],
			$validator->getInvalidPredicates()
		);
	}

}
