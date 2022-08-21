<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Presentation\HtmlPredicatesPresenter;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Presentation\HtmlPredicatesPresenter
 */
class HtmlPredicatesPresenterTest extends TestCase {

	private const LOCAL_SETTINGS_MESSAGE = '<code>*</code> Defined in LocalSettings.php';

	public function testHtmlContainsOnlyLocalSettingsPredicates(): void {
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates(
			new PredicateList( [ new Predicate( 'foo:bar' ), new Predicate( 'bar:baz' ) ] ),
			new PredicateList()
		);

		$html = $presenter->getHtml();

		$this->assertStringContainsString( '<li><span>foo:bar</span> <code>*</code></li>', $html );
		$this->assertStringContainsString( '<li><span>bar:baz</span> <code>*</code></li>', $html );
		$this->assertStringContainsString( self::LOCAL_SETTINGS_MESSAGE, $html );
	}

	public function testHtmlContainsOnlyWikiConfigPredicates(): void {
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates(
			new PredicateList(),
			new PredicateList( [ new Predicate( 'foo:bar' ), new Predicate( 'bar:baz' ) ] )
		);

		$html = $presenter->getHtml();

		$this->assertStringContainsString( '<li>foo:bar</li>', $html );
		$this->assertStringContainsString( '<li>bar:baz</li>', $html );
		$this->assertStringNotContainsString( self::LOCAL_SETTINGS_MESSAGE, $html );
	}

	public function testHtmlContainsLocalSettingsAndWikiConfigPredicates(): void {
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates(
			new PredicateList( [ new Predicate( 'foo:bar' ) ] ),
			new PredicateList( [ new Predicate( 'bar:baz' ) ] )
		);

		$html = $presenter->getHtml();

		$this->assertStringContainsString( '<li><span>foo:bar</span> <code>*</code></li>', $html );
		$this->assertStringContainsString( '<li>bar:baz</li>', $html );
		$this->assertStringContainsString( self::LOCAL_SETTINGS_MESSAGE, $html );
	}

	public function testHtmlContainsMessageIfNoPredicatesAreConfigured(): void {
		$presenter = new HtmlPredicatesPresenter();
		$presenter->presentPredicates( new PredicateList(), new PredicateList() );

		$html = $presenter->getHtml();

		$this->assertStringContainsString(
			'No predicates are defined.',
			$html
		);
		$this->assertStringNotContainsString( self::LOCAL_SETTINGS_MESSAGE, $html );
	}

}
