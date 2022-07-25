<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRoleRegistry;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MultiEntityRdfBuilder;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Rdf\EntityRdfBuilder;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikimedia\Purtle\RdfWriter;

class MediaWikiHooks {

	public static function onMediaWikiServices( MediaWikiServices $services ): void {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function ( SlotRoleRegistry $registry ) {
				$registry->defineRoleWithModel( WikibaseRdfExtension::SLOT_NAME, CONTENT_MODEL_JSON );
			} );
	}

	// TODO: is this the best hook?
	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		if ( $out->getTitle()?->inNamespaces( WB_NS_ITEM, WB_NS_PROPERTY ) ) {
			// TODO: load styles earlier because this causes the initial content to be unstyled
			$out->addModules( 'ext.wikibase.rdf' );

			$presenter = WikibaseRdfExtension::getInstance()->newStubMappingsPresenter();
			$useCase = WikibaseRdfExtension::getInstance()->newShowMappingsUseCase( $presenter, $out->getUser() );
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
			WikibaseRdfExtension::getInstance()->newMappingRdfBuilder( $writer )
		);
	}

}
