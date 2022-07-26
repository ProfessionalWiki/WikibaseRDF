<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Tests\Application\SaveMappings;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;
use ProfessionalWiki\WikibaseRDF\Application\SaveMappings\SaveMappingsUseCase;
use ProfessionalWiki\WikibaseRDF\Persistence\InMemoryMappingRepository;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\SpySaveMappingsPresenter;
use ProfessionalWiki\WikibaseRDF\Tests\TestDoubles\ThrowingMappingRepository;
use RuntimeException;
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
			[ self::VALID_PREDICATE ]
		);
	}

	private function newItemId(): ItemId {
		return new ItemId( 'Q1' );
	}

	public function testShouldShowSuccess(): void {
		$useCase = $this->newUseCase();

		$useCase->saveMappings(
			$this->newItemId(),
			new MappingList( [
				new Mapping( self::VALID_PREDICATE, self::VALID_OBJECT )
			] )
		);

		$this->assertTrue( $this->presenter->showedSuccess );
		$this->assertNull( $this->presenter->invalidMappings );
		$this->assertFalse( $this->presenter->showedSaveFailed );
	}

	public function testMappingIsPersisted(): void {
		$useCase = $this->newUseCase();
		$entityId = $this->newItemId();

		$useCase->saveMappings(
			$entityId,
			new MappingList( [
				new Mapping( self::VALID_PREDICATE, self::VALID_OBJECT )
			] )
		);

		$mappings = $this->repository->getMappings( $entityId );

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
			$this->newItemId(),
			new MappingList( [
				new Mapping( self::VALID_PREDICATE, self::VALID_OBJECT ),
				new Mapping( self::INVALID_PREDICATE, self::VALID_OBJECT )
			] )
		);

		$this->assertFalse( $this->presenter->showedSuccess );
		$this->assertSame(
			self::INVALID_PREDICATE,
			$this->presenter->invalidMappings->asArray()[0]->predicate
		);
		$this->assertSame(
			self::VALID_OBJECT,
			$this->presenter->invalidMappings->asArray()[0]->object
		);
		$this->assertFalse( $this->presenter->showedSaveFailed );
	}

	public function testShouldShowSaveFailed(): void {
		$useCase = new SaveMappingsUseCase(
			$this->presenter,
			new ThrowingMappingRepository(),
			[ self::VALID_PREDICATE ]
		);

		$useCase->saveMappings(
			$this->newItemId(),
			new MappingList( [
				new Mapping( self::VALID_PREDICATE, self::VALID_OBJECT )
			] )
		);

		$this->assertFalse( $this->presenter->showedSuccess );
		$this->assertNull( $this->presenter->invalidMappings );
		$this->assertTrue( $this->presenter->showedSaveFailed );
	}

}
