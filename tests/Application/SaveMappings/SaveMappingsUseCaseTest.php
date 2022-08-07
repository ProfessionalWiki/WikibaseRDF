<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application\SaveMappings;

use PermissionsError;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\MappingListSerializer;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\PermissionDeniedMappingRepository;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\SpySaveMappingsPresenter;
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

	private SpySaveMappingsPresenter $presenter;
	private InMemoryMappingRepository $repository;

	public function setUp(): void {
		$this->presenter = new SpySaveMappingsPresenter();
		$this->repository = new InMemoryMappingRepository();
	}

	private function newUseCase(): SaveMappingsUseCase {
		return new SaveMappingsUseCase(
			$this->presenter,
			$this->repository,
			[ self::VALID_PREDICATE ],
			new BasicEntityIdParser(),
			new MappingListSerializer()
		);
	}

	public function testShouldShowSuccess(): void {
		$useCase = $this->newUseCase();

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
		$useCase = $this->newUseCase();

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

	public function testShouldShowInvalidMappings(): void {
		$useCase = $this->newUseCase();

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

	public function testShouldShowSaveFailed(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			new ThrowingMappingRepository(),
			[ self::VALID_PREDICATE ],
			new BasicEntityIdParser(),
			new MappingListSerializer()
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
			[ self::VALID_PREDICATE ],
			new BasicEntityIdParser(),
			new MappingListSerializer()
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
			[ self::VALID_PREDICATE ],
			new BasicEntityIdParser(),
			new MappingListSerializer()
		);

		$useCase->saveMappings(
			'Q1',
			[
				[ 'predicate' => self::VALID_PREDICATE, 'object' => self::VALID_OBJECT ]
			]
		);

		$this->assertEquals(
			new PermissionsError( 'edit' ),
			$this->presenter->permissionDeniedException
		);
	}

}
