<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\Predicate
 */
class PredicateTest extends TestCase {

	public function testCanConstruct(): void {
		$predicate = new Predicate( 'foo:bar' );
		$this->assertEquals( 'foo:bar', $predicate->predicate );
	}

	public function testConstructorThrowsException(): void {
		$this->expectException( InvalidArgumentException::class );
		new Predicate( 'notvalid' );
	}

}
