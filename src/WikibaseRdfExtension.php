<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\Application\ShowMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\EntityContentRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository;
use ProfessionalWiki\WikibaseRDF\Presentation\MappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MappingRdfBuilder;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetMappingsApi;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\SaveMappingsApi;
use ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter;
use RequestContext;
use User;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Purtle\RdfWriter;

/**
 * Top level factory for the WikibaseRDF extension
 */
class WikibaseRdfExtension {

	public const SLOT_NAME = 'wikibase-rdf';

	public static function getInstance(): self {
		/** @var ?WikibaseRdfExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public function newStubMappingsPresenter(): StubMappingsPresenter {
		return new StubMappingsPresenter( self::getAllowedPredicates() );
	}

	public function newShowMappingsUseCase( MappingsPresenter $presenter, User $user ): ShowMappingsUseCase {
		return new ShowMappingsUseCase(
			$presenter,
			$this->newMappingRepository( $user )
		);
	}

	private function newEntityContentRepository( User $user ): EntityContentRepository {
		return new SlotEntityContentRepository(
			authority: $user,
			pageFactory: MediaWikiServices::getInstance()->getWikiPageFactory(),
			entityTitleLookup: WikibaseRepo::getEntityTitleLookup(),
			slotName: self::SLOT_NAME
		);
	}

	public function newMappingRepository( User $user ): MappingRepository {
		return new ContentSlotMappingRepository(
			contentRepository: $this->newEntityContentRepository( $user ),
			serializer: $this->newMappingListSerializer()
		);
	}

	public function newMappingListSerializer(): MappingListSerializer {
		return new MappingListSerializer();
	}

	public function newMappingRdfBuilder( RdfWriter $writer ): MappingRdfBuilder {
		return new MappingRdfBuilder(
			$writer,
			$this->newMappingRepository( RequestContext::getMain()->getUser() )
		);
	}

	public function newEntityIdParser(): EntityIdParser {
		return new BasicEntityIdParser();
	}

	public static function getMappingsApiFactory(): GetMappingsApi {
		return self::getInstance()->newGetMappingsApi();
	}

	private function newGetMappingsApi(): GetMappingsApi {
		return new GetMappingsApi(
			$this->newEntityIdParser(),
			$this->newMappingRepository(
				RequestContext::getMain()->getUser()
			),
			$this->newMappingListSerializer()
		);
	}

	public static function saveMappingsApiFactory(): SaveMappingsApi {
		return self::getInstance()->newSaveMappingsApi();
	}

	private function newSaveMappingsApi(): SaveMappingsApi {
		return new SaveMappingsApi(
			$this->newEntityIdParser(),
			$this->newMappingRepository(
				RequestContext::getMain()->getUser()
			),
			$this->newMappingListSerializer()
		);
	}

	/**
	 * @return string[]
	 */
	private static function getAllowedPredicates(): array {
		return (array)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseRdfPredicates' );
	}

}
