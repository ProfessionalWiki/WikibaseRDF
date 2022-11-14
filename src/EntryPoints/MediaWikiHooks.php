<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints;

use EditPage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRoleRegistry;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MultiEntityRdfBuilder;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use Title;
use User;
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

		$presenter = WikibaseRdfExtension::getInstance()->newHtmlMappingsPresenter(
			 $page->getRequest()->getCheck( 'diff' )
		);

		$repository = WikibaseRdfExtension::getInstance()->newMappingRepository( $page->getUser() );
		$authorizer = WikibaseRdfExtension::getInstance()->newEntityMappingsAuthorizer( $page->getUser() );

		$presenter->showMappings(
			$repository->getMappings( $entityId, $page->getRevisionId() ?? 0 ),
			$authorizer->canEditEntityMappings( $entityId )
		);

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
				$error = self::createInvalidPredicatesError( $validator->getInvalidPredicates() );
			}
		}
	}

	/**
	 * @param string[] $invalidPredicates
	 */
	private static function createInvalidPredicatesError( array $invalidPredicates ): string {
		$imploded = implode(
			', ',
			array_map(
				fn( string $predicate ) => '"' . $predicate . '"',
				$invalidPredicates
			)
		);
		return \Html::errorBox(
			wfMessage( 'wikibase-rdf-config-invalid', count( $invalidPredicates ), $imploded )->escaped()
		);
	}

	public static function onAlternateEdit( EditPage $editPage ): void {
		if ( WikibaseRdfExtension::getInstance()->isConfigTitle( $editPage->getTitle() ) ) {
			$editPage->suppressIntro = true;
			$editPage->editFormTextBeforeContent = wfMessage( 'wikibase-rdf-config-intro' );
		}
	}

	public static function onArticleRevisionViewCustom(
		RevisionRecord $revision,
		Title $title,
		int $oldId,
		OutputPage $output
	): bool {
		if ( WikibaseRdfExtension::getInstance()->isConfigTitle( $title ) ) {
			$localSettingsLookup = WikibaseRdfExtension::getInstance()->newLocalSettingsMappingPredicatesLookup();
			$wikiSettingsLookup = WikibaseRdfExtension::getInstance()->newWikiMappingPredicatesLookup();
			$presenter = WikibaseRdfExtension::getInstance()->newHtmlPredicatesPresenter();
			$presenter->presentPredicates(
				$localSettingsLookup->getMappingPredicates(),
				$wikiSettingsLookup->getMappingPredicates()
			);
			$output->clearHTML();
			$output->addHTML( $presenter->getHtml() );
			return false;
		}
		return true;
	}

	/**
	 * @param array<string, mixed> $preferences
	 */
	public static function onGetPreferences( User $user, array &$preferences ): void {
		$preferences['wikibase-rdf-acknowledge-property-edit'] = [
			'type' => 'api'
		];
	}

}
