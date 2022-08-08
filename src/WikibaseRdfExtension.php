<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\ResponseFactory;
use ProfessionalWiki\WikibaseRDF\Application\AllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\Application\ShowMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\DataAccess\CombiningMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\MappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\PageContentFetcher;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;
use ProfessionalWiki\WikibaseRDF\DataAccess\WikiMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetAllMappingsApi;
use ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Presentation\MappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MappingRdfBuilder;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetMappingsApi;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\SaveMappingsApi;
use ProfessionalWiki\WikibaseRDF\Presentation\RestSaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Presentation\StubMappingsPresenter;
use RequestContext;
use Title;
use User;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Purtle\RdfWriter;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Top level factory for the WikibaseRDF extension
 */
class WikibaseRdfExtension {

	public const SLOT_NAME = 'wikibase-rdf';
	public const CONFIG_PAGE_TITLE = 'MappingPredicates';

	public static function getInstance(): self {
		/** @var ?WikibaseRdfExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public function newStubMappingsPresenter(): StubMappingsPresenter {
		return new StubMappingsPresenter(
			$this->newMappingPredicatesLookup()->getMappingPredicates()
		);
	}

	public function newShowMappingsUseCase( MappingsPresenter $presenter, User $user ): ShowMappingsUseCase {
		return new ShowMappingsUseCase(
			$presenter,
			$this->newMappingRepository( $user )
		);
	}

	public function newEntityContentRepository( Authority $authority ): SlotEntityContentRepository {
		return new SlotEntityContentRepository(
			authority: $authority,
			pageFactory: MediaWikiServices::getInstance()->getWikiPageFactory(),
			entityTitleLookup: WikibaseRepo::getEntityTitleLookup(),
			slotName: self::SLOT_NAME
		);
	}

	public function newMappingRepository( Authority $authority ): MappingRepository {
		return new ContentSlotMappingRepository(
			contentRepository: $this->newEntityContentRepository( $authority ),
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
		return new SaveMappingsApi();
	}

	public static function getAllMappingsApiFactory(): GetAllMappingsApi {
		return self::getInstance()->newGetAllMappingsApi();
	}

	private function newGetAllMappingsApi(): GetAllMappingsApi {
		return new GetAllMappingsApi(
			$this->newAllMappingsLookup(),
			$this->newMappingListSerializer()
		);
	}

	private function newAllMappingsLookup(): AllMappingsLookup {
		return new SqlAllMappingsLookup(
			MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnectionRef( ILoadBalancer::DB_REPLICA ),
			self::SLOT_NAME,
			$this->newEntityIdParser(),
			$this->newMappingListSerializer()
		);
	}

	public function newSaveMappingsUseCase(
		SaveMappingsPresenter $presenter,
		Authority $authority
	): SaveMappingsUseCase {
		return new SaveMappingsUseCase(
			$presenter,
			$this->newMappingRepository( $authority ),
			$this->newMappingPredicatesLookup()->getMappingPredicates(),
			$this->newEntityIdParser(),
			$this->newMappingListSerializer()
		);
	}

	public function newRestSaveMappingsPresenter( ResponseFactory $responseFactory ): RestSaveMappingsPresenter {
		return new RestSaveMappingsPresenter(
			$responseFactory
		);
	}

	public function newMappingPredicatesLookup(): MappingPredicatesLookup {
		return new CombiningMappingPredicatesLookup(
			(array)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseRdfPredicates' ),
			new WikiMappingPredicatesLookup(
				new PageContentFetcher(
					MediaWikiServices::getInstance()->getTitleParser(),
					MediaWikiServices::getInstance()->getRevisionLookup()
				),
				new PredicatesDeserializer(
					new PredicatesTextValidator()
				),
				self::CONFIG_PAGE_TITLE
			)
		);
	}

	public function isConfigTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI
			&& $title->getText() === self::CONFIG_PAGE_TITLE;
	}

}
