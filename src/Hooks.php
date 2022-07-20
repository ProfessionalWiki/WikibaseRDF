<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use OutputPage;
use ParserOutput;
use Wikibase\DataModel\Entity\ItemId;

class Hooks {

	// TODO: is this the best hook?
	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		if ( $out->getTitle()?->inNamespaces( WB_NS_ITEM, WB_NS_PROPERTY ) ) {
			// TODO: load styles earlier because this causes the initial content to be unstyled
			$out->addModules( 'ext.wikibase.rdf' );

			$presenter = WikibaseRDFExtension::getInstance()->newStubMappingsPresenter();
			$useCase = WikibaseRDFExtension::getInstance()->newShowMappingsUseCase( $presenter, $out->getUser() );
			// TODO: get actual ID
			$useCase->showMappings( new ItemId( 'Q1' ) );

			$out->addHTML( $presenter->getHtml() );
		}
	}

}
