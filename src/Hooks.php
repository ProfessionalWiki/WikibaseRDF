<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use OutputPage;
use ParserOutput;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MultiEntityRdfBuilder;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Rdf\EntityRdfBuilder;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikimedia\Purtle\RdfWriter;

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

	/**
	 * @param array<string, array<string, callable>> $entityTypeDefinitions
	 */
	public static function onWikibaseRepoEntityTypes( array &$entityTypeDefinitions ): void {
		$entityTypeDefinitions['item'][EntityTypeDefinitions::RDF_BUILDER_FACTORY_CALLBACK]
			= self::newEntityRdfBuilderFactoryFunction( $entityTypeDefinitions['item'][EntityTypeDefinitions::RDF_BUILDER_FACTORY_CALLBACK] );

		$entityTypeDefinitions['property'][EntityTypeDefinitions::RDF_BUILDER_FACTORY_CALLBACK]
			= self::newEntityRdfBuilderFactoryFunction( $entityTypeDefinitions['property'][EntityTypeDefinitions::RDF_BUILDER_FACTORY_CALLBACK] );
	}

	private static function newEntityRdfBuilderFactoryFunction( callable $factoryFunction ): callable {
		return fn(
			int $flavorFlags,
			RdfVocabulary $vocabulary,
			RdfWriter $writer
		): EntityRdfBuilder => new MultiEntityRdfBuilder(
			$factoryFunction( ...func_get_args() ),
			WikibaseRDFExtension::getInstance()->newMappingRdfBuilder( $writer )
		);
	}

}
