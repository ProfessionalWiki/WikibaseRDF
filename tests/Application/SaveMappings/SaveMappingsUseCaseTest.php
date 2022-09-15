<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application\SaveMappings;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\EntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Application\Predicate;
use ProfessionalWiki\WikibaseRDF\Application\PredicateList;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\FailingEntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\FailingObjectValidator;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\PermissionDeniedMappingRepository;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\SpySaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\SucceedingEntityMappingsAuthorizer;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\SucceedingObjectValidator;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\ThrowingMappingRepository;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase
 */
class SaveMappingsUseCaseTest extends TestCase {

	private const VALID_PREDICATE = 'owl:sameAs';
	private const INVALID_PREDICATE = 'invalid:predicate';
	private const VALID_OBJECT = 'http://www.w3.org/2000/01/rdf-schema#subClassOf';
	private const INVALID_OBJECT = 'owl:subClassOf';

	private SpySaveMappingsPresenter $presenter;
	private InMemoryMappingRepository $repository;

	public function setUp(): void {
		$this->presenter = new SpySaveMappingsPresenter();
		$this->repository = new InMemoryMappingRepository();
	}

	private function newSucceedingAuthorizer(): EntityMappingsAuthorizer {
		return new SucceedingEntityMappingsAuthorizer();
	}

	private function newFailingAuthorizer(): EntityMappingsAuthorizer {
		return new FailingEntityMappingsAuthorizer();
	}

	private function newUseCase( EntityMappingsAuthorizer $authorizer ): SaveMappingsUseCase {
		return new SaveMappingsUseCase(
			$this->presenter,
			$this->repository,
			new PredicateList( [ new Predicate( self::VALID_PREDICATE ) ] ),
			new BasicEntityIdParser(),
			new MappingListSerializer(),
			$authorizer,
			new SucceedingObjectValidator()
		);
	}

	public function testShouldShowSuccess(): void {
		$useCase = $this->newUseCase( $this->newSucceedingAuthorizer() );

		$useCase->saveMappings(
			'Q1',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$this->assertTrue( $this->presenter->showedSuccess );
		$this->assertSame( [], $this->presenter->invalidMappings );
		$this->assertFalse( $this->presenter->showedSaveFailed );
	}

	public function testMappingIsPersisted(): void {
		$useCase = $this->newUseCase( $this->newSucceedingAuthorizer() );

		$useCase->saveMappings(
			'Q2',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$mappings = $this->repository->getMappings( new ItemId( 'Q2' ) );

		$this->assertSame(
			self::VALID_PREDICATE,
			$mappings->asArray()[0]->predicate
		);
		$this->assertSame(
			self::VALID_OBJECT,
			$mappings->asArray()[0]->object
		);
	}

	public function testShouldShowInvalidMappingsWhenPredicateIsInvalid(): void {
		$useCase = $this->newUseCase( $this->newSucceedingAuthorizer() );

		$useCase->saveMappings(
			'Q3',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ],
				[ 'predicate' => self::INVALID_PREDICATE, 'object' => self::VALID_OBJECT ],
			]
		);

		$this->assertFalse( $this->presenter->showedSuccess );
		$this->assertSame(
			self::INVALID_PREDICATE,
			$this->presenter->invalidMappings[0]['predicate']
		);
		$this->assertSame(
			self::VALID_OBJECT,
			$this->presenter->invalidMappings[0]['object']
		);
		$this->assertFalse( $this->presenter->showedSaveFailed );
	}

	public function testShouldShowInvalidMappingsWhenObjectIsInvalid(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			$this->repository,
			new PredicateList( [ new Predicate( self::VALID_PREDICATE ) ] ),
			new BasicEntityIdParser(),
			new MappingListSerializer(),
			$this->newSucceedingAuthorizer(),
			new FailingObjectValidator()
		);

		$useCase->saveMappings(
			'Q3',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::INVALID_OBJECT ],
			]
		);

		$this->assertFalse( $this->presenter->showedSuccess );
		$this->assertSame(
			self::VALID_PREDICATE,
			$this->presenter->invalidMappings[0]['predicate']
		);
		$this->assertSame(
			self::INVALID_OBJECT,
			$this->presenter->invalidMappings[0]['object']
		);
		$this->assertFalse( $this->presenter->showedSaveFailed );
	}

	public function testShouldShowSaveFailed(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			new ThrowingMappingRepository(),
			new PredicateList( [ new Predicate( self::VALID_PREDICATE ) ] ),
			new BasicEntityIdParser(),
			new MappingListSerializer(),
			$this->newSucceedingAuthorizer(),
			new SucceedingObjectValidator()
		);

		$useCase->saveMappings(
			'Q4',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$this->assertFalse( $this->presenter->showedSuccess );
		$this->assertSame( [], $this->presenter->invalidMappings );
		$this->assertTrue( $this->presenter->showedSaveFailed );
	}

	public function testShouldShowInvalidEntityId(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			new ThrowingMappingRepository(),
			new PredicateList( [ new Predicate( self::VALID_PREDICATE ) ] ),
			new BasicEntityIdParser(),
			new MappingListSerializer(),
			$this->newSucceedingAuthorizer(),
			new SucceedingObjectValidator()
		);

		$useCase->saveMappings(
			'NotId',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$this->assertTrue( $this->presenter->showedInvalidEntityId );
	}

	public function testShouldShowPermissionDenied(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			new PermissionDeniedMappingRepository(),
			new PredicateList( [ new Predicate( self::VALID_PREDICATE ) ] ),
			new BasicEntityIdParser(),
			new MappingListSerializer(),
			$this->newFailingAuthorizer(),
			new SucceedingObjectValidator()
		);

		$useCase->saveMappings(
			'Q1',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$this->assertTrue( $this->presenter->showedPermissionDenied );
	}

}
