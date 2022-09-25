<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\ResponseFactory;
use ProfessionalWiki\WikibaseRDF\Application\AllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Application\EntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Application\MappingRepository;
use ProfessionalWiki\WikibaseRDF\Application\WikibaseUrlObjectValidator;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\DataAccess\CombiningMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\LocalSettingsMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\MappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\DataAccess\PageContentFetcher;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesDeserializer;
use ProfessionalWiki\WikibaseRDF\DataAccess\PredicatesTextValidator;
use ProfessionalWiki\WikibaseRDF\DataAccess\WikiMappingPredicatesLookup;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetAllMappingsApi;
use ProfessionalWiki\WikibaseRDF\Persistence\ContentSlotMappingRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\SlotEntityContentRepository;
use ProfessionalWiki\WikibaseRDF\Persistence\SqlAllMappingsLookup;
use ProfessionalWiki\WikibaseRDF\Persistence\UserBasedEntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Presentation\HtmlPredicatesPresenter;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\MappingRdfBuilder;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\GetMappingsApi;
use ProfessionalWiki\WikibaseRDF\EntryPoints\Rest\SaveMappingsApi;
use ProfessionalWiki\WikibaseRDF\Presentation\RDF\PropertyMappingPrefixBuilder;
use ProfessionalWiki\WikibaseRDF\Presentation\RestSaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Presentation\HtmlMappingsPresenter;
use RequestContext;
use Title;
use User;
use ValueValidators\ValueValidator;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Repo\Store\EntityPermissionChecker;
use Wikibase\Repo\Store\WikiPageEntityStorePermissionChecker;
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

	public function newHtmlMappingsPresenter( bool $isDiffPage ): HtmlMappingsPresenter {
		return new HtmlMappingsPresenter(
			$this->getAllowedPredicates(),
			$isDiffPage
		);
	}

	public function newEntityContentRepository( Authority $authority ): SlotEntityContentRepository {
		return new SlotEntityContentRepository(
			authority: $authority,
			pageFactory: MediaWikiServices::getInstance()->getWikiPageFactory(),
			entityTitleLookup: WikibaseRepo::getEntityTitleLookup(),
			slotName: self::SLOT_NAME,
			revisionLookup: MediaWikiServices::getInstance()->getRevisionLookup()
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
			$this->newMappingRepository( RequestContext::getMain()->getUser() ),
			$this->newPropertyMappingPrefixBuilder()
		);
	}

	private function newPropertyMappingPrefixBuilder(): PropertyMappingPrefixBuilder {
		return new PropertyMappingPrefixBuilder(
			WikibaseRepo::getLocalEntitySource()->getRdfNodeNamespacePrefix()
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

	private function getAllowedPredicates(): PredicateList {
		return $this->newMappingPredicatesLookup()->getMappingPredicates();
	}

	public function newSaveMappingsUseCase(
		SaveMappingsPresenter $presenter,
		User $user
	): SaveMappingsUseCase {
		return new SaveMappingsUseCase(
			$presenter,
			$this->newMappingRepository( $user ),
			$this->getAllowedPredicates(),
			$this->newEntityIdParser(),
			$this->newMappingListSerializer(),
			$this->newEntityMappingsAuthorizer( $user ),
			$this->newObjectValidator()
		);
	}

	public function newRestSaveMappingsPresenter( ResponseFactory $responseFactory ): RestSaveMappingsPresenter {
		return new RestSaveMappingsPresenter(
			$responseFactory
		);
	}

	public function newLocalSettingsMappingPredicatesLookup(): LocalSettingsMappingPredicatesLookup {
		return new LocalSettingsMappingPredicatesLookup(
			(array)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseRdfPredicates' )
		);
	}

	public function newWikiMappingPredicatesLookup(): WikiMappingPredicatesLookup {
		return new WikiMappingPredicatesLookup(
			new PageContentFetcher(
				MediaWikiServices::getInstance()->getTitleParser(),
				MediaWikiServices::getInstance()->getRevisionLookup()
			),
			new PredicatesDeserializer(
				new PredicatesTextValidator()
			),
			self::CONFIG_PAGE_TITLE
		);
	}

	public function newMappingPredicatesLookup(): CombiningMappingPredicatesLookup {
		return new CombiningMappingPredicatesLookup(
			$this->newLocalSettingsMappingPredicatesLookup(),
			$this->newWikiMappingPredicatesLookup()
		);
	}

	public function isConfigTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI
			&& $title->getText() === self::CONFIG_PAGE_TITLE;
	}

	public function newEntityPermissionChecker( MediaWikiServices $services ): EntityPermissionChecker {
		return new WikiPageEntityStorePermissionChecker(
			WikibaseRepo::getEntityNamespaceLookup( $services ),
			WikibaseRepo::getEntityTitleLookup( $services ),
			$services->getPermissionManager(),
			(array)$services->getMainConfig()->get( 'AvailableRights' )
		);
	}

	public function newEntityMappingsAuthorizer( User $user ): EntityMappingsAuthorizer {
		return new UserBasedEntityMappingsAuthorizer(
			$user,
			$this->newEntityPermissionChecker( MediaWikiServices::getInstance() )
		);
	}

	public function newHtmlPredicatesPresenter(): HtmlPredicatesPresenter {
		return new HtmlPredicatesPresenter();
	}

	/**
	 * @return ValueValidator[]
	 */
	private function newUrlValidators(): array {
		$factory = WikibaseRepo::getDefaultValidatorBuilders();
		$constraints = (array)WikibaseRepo::getSettings()->getSetting( 'string-limits' )['PT:url'];
		$maxLength = (int)$constraints['length'];
		return $factory->buildUrlValidators( $maxLength );
	}

	public function newObjectValidator(): WikibaseUrlObjectValidator {
		return new WikibaseUrlObjectValidator(
			$this->newUrlValidators()
		);
	}

}
