<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Presentation\RDF;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\PropertyMappingPrefixBuilder;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\RDF\PropertyMappingPrefixBuilder
 */
class PropertyMappingPrefixBuilderTest extends TestCase {

	public function testGetPrefix(): void {
		$builder = new PropertyMappingPrefixBuilder( 'wd' );

		$this->assertSame( 'wdt', $builder->getPrefix() );
	}

}
