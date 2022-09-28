<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Presentation\HtmlMappingsPresenter;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\HtmlMappingsPresenter
 */
class HtmlMappingsPresenterTest extends TestCase {

	private const CLASS_ACTION_EDIT = 'wikibase-rdf-action-edit';
	private const CLASS_ACTION_SAVE = 'wikibase-rdf-action-save';
	private const CLASS_ACTION_REMOVE = 'wikibase-rdf-action-remove';
	private const CLASS_ACTION_CANCEL = 'wikibase-rdf-action-cancel';
	private const CLASS_ACTION_ADD = 'wikibase-rdf-action-add';

	private function getAllowedPredicates(): PredicateList {
		return new PredicateList( [
			new Predicate( 'owl:sameAs' ),
			new Predicate( 'skos:exactMatch' ),
		] );
	}

	public function testMappingValuesAreDisplayed(): void {
		$presenter = new HtmlMappingsPresenter( $this->getAllowedPredicates(), false );
		$mapping1 = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
		$mapping2 = new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' );

		$presenter->showMappings(
			new MappingList( [ $mapping1, $mapping2 ] ),
			true
		);
		$html = $presenter->getHtml();

		$this->assertStringContainsString( $mapping1->predicate, $html );
		$this->assertStringContainsString( $mapping1->object, $html );
		$this->assertStringContainsString( $mapping2->predicate, $html );
		$this->assertStringContainsString( $mapping2->object, $html );
	}

	public function testMappingUrlIsALink(): void {
		$presenter = new HtmlMappingsPresenter( $this->getAllowedPredicates(), false );
		$mapping = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );

		$presenter->showMappings(
			new MappingList( [ $mapping ] ),
			true
		);
		$html = $presenter->getHtml();

		$this->assertStringContainsString( '<a href="' . $mapping->object . '"', $html );
	}

	public function testEditActionsAreDisplayed(): void {
		$presenter = new HtmlMappingsPresenter( $this->getAllowedPredicates(), false );
		$mapping1 = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
		$mapping2 = new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' );

		$presenter->showMappings(
			new MappingList( [ $mapping1, $mapping2 ] ),
			true
		);
		$html = $presenter->getHtml();

		$this->assertStringContainsString( self::CLASS_ACTION_EDIT, $html );
		$this->assertStringContainsString( self::CLASS_ACTION_SAVE, $html );
		$this->assertStringContainsString( self::CLASS_ACTION_REMOVE, $html );
		$this->assertStringContainsString( self::CLASS_ACTION_CANCEL, $html );
		$this->assertStringContainsString( self::CLASS_ACTION_ADD, $html );
	}

	public function testEditActionsAreNotDisplayedWhenUserCannotEdit(): void {
		$presenter = new HtmlMappingsPresenter( $this->getAllowedPredicates(), false );
		$mapping1 = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
		$mapping2 = new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' );

		$presenter->showMappings(
			new MappingList( [ $mapping1, $mapping2 ] ),
			false
		);
		$html = $presenter->getHtml();

		$this->assertStringNotContainsString( self::CLASS_ACTION_EDIT, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_SAVE, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_REMOVE, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_CANCEL, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_ADD, $html );
	}

	public function testEditActionsAreNotDisplayedOnDiffPage(): void {
		$presenter = new HtmlMappingsPresenter( $this->getAllowedPredicates(), true );
		$mapping1 = new Mapping( 'owl:sameAs', 'http://www.w3.org/2000/01/rdf-schema#subClassOf' );
		$mapping2 = new Mapping( 'skos:exactMatch', 'http://www.example.com/foo' );

		$presenter->showMappings(
			new MappingList( [ $mapping1, $mapping2 ] ),
			true
		);
		$html = $presenter->getHtml();

		$this->assertStringNotContainsString( self::CLASS_ACTION_EDIT, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_SAVE, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_REMOVE, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_CANCEL, $html );
		$this->assertStringNotContainsString( self::CLASS_ACTION_ADD, $html );
	}

}
