<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints;

use EditPage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRoleRegistry;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MultiEntityRdfBuilder;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Title;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Rdf\EntityRdfBuilder;
use Wikibase\Repo\Rdf\RdfVocabulary;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Purtle\RdfWriter;

class MediaWikiHooks {

	public static function onMediaWikiServices( MediaWikiServices $services ): void {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function ( SlotRoleRegistry $registry ) {
				$registry->defineRoleWithModel(
					WikibaseRdfExtension::SLOT_NAME,
					CONTENT_MODEL_JSON,
					[ 'display' => 'none' ]
				);
			}
		);
	}

	// TODO: is this the best hook?
	public static function onOutputPageParserOutput( OutputPage $page, ParserOutput $parserOutput ): void {
		$entityId = self::getEntityIdForOutputPage( $page );

		if ( $entityId !== null ) {
			self::addMappingUi( $page, $entityId );
		}
	}

	private static function getEntityIdForOutputPage( OutputPage $page ): ?EntityId {
		$title = $page->getTitle();

		if ( $title === null
			|| !$title->inNamespaces( WB_NS_ITEM, WB_NS_PROPERTY )
			|| !$title->exists() ) {
			return null;
		}

		try {
			return WikibaseRepo::getEntityIdParser()->parse( $title->getText() );
		}
		catch ( EntityIdParsingException ) {
			return null;
		}
	}

	private static function addMappingUi( OutputPage $page, EntityId $entityId ): void {
		// TODO: load styles earlier because this causes the initial content to be unstyled
		$page->addModules( 'ext.wikibase.rdf' );

		$presenter = WikibaseRdfExtension::getInstance()->newStubMappingsPresenter();

		$useCase = WikibaseRdfExtension::getInstance()->newShowMappingsUseCase( $presenter, $page->getUser() );
		$useCase->showMappings( $entityId );

		$page->addHTML( $presenter->getHtml() );
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

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( WikibaseRdfExtension::getInstance()->isConfigTitle( $title ) ) {
			$model = CONTENT_MODEL_TEXT;
		}
	}

	public static function onEditFilter( EditPage $editPage, ?string $text, ?string $section, string &$error ): void {
		if ( is_string( $text ) && WikibaseRdfExtension::getInstance()->isConfigTitle( $editPage->getTitle() ) ) {
			$validator = new PredicatesTextValidator();
			if ( !$validator->validate( $text ) ) {
				$invalidPredicates = join(
					', ',
					array_map(
						fn( string $predicate) => '"' . $predicate . '"' ,
						$validator->getInvalidPredicates()
					)
				);
				$error = \Html::errorBox(
					wfMessage( 'wikibase-rdf-config-invalid', $invalidPredicates )->escaped()
				);
			}
		}
	}

	public static function onAlternateEdit( EditPage $editPage ): void {
		if ( WikibaseRdfExtension::getInstance()->isConfigTitle( $editPage->getTitle() ) ) {
			$editPage->suppressIntro = true;
		}
	}

}
