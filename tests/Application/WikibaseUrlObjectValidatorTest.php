<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\WikibaseUrlObjectValidator;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\WikibaseUrlObjectValidator
 */
class WikibaseUrlObjectValidatorTest extends TestCase {

	private function createValidator(): WikibaseUrlObjectValidator {
		return WikibaseRdfExtension::getInstance()->newObjectValidator();
	}

	public function provideValidObjects(): iterable {
		yield 'Standard URL' => [ 'http://www.w3.org/2002/07/owl#sameAs' ];
		yield 'Other protocol' => [ 'ftp://example.com' ];
		yield 'Without TLD' => [ 'http://example' ];
		yield 'Non-ASCII' => [ 'http://exåmple.com' ];
	}

	/**
	 * @dataProvider provideValidObjects
	 */
	public function testIsValid( string $object ): void {
		$validator = $this->createValidator();

		$this->assertTrue( $validator->isValid( $object ) );
	}

	public function provideInvalidObjects(): iterable {
		yield 'Missing protocol' => [ 'example.com' ];
		yield 'Missing slash' => [ 'http:/example.com' ];
	}

	/**
	 * @dataProvider provideInvalidObjects
	 */
	public function testIsInvalid( string $object ): void {
		$validator = $this->createValidator();

		$this->assertFalse( $validator->isValid( $object ) );
	}

}
