<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use ProfessionalWiki\WikibaseRDF\Presentation\MappingsPresenter;
use Wikibase\DataModel\Entity\EntityId;

class ShowMappingsUseCase {

	public function __construct(
		private MappingsPresenter $presenter,
		private MappingRepository $repository,
		private EntityMappingsAuthorizer $authorizer
	) {
	}

	public function showMappings( EntityId $entityId ): void {
		$this->presenter->showMappings( $this->getMappings( $entityId ), $this->canEdit( $entityId ) );
	}

	private function getMappings( EntityId $entityId ): MappingList {
		return $this->repository->getMappings( $entityId );
	}

	private function canEdit( EntityId $entityId ): bool {
		return $this->authorizer->canEditEntityMappings( $entityId );
	}

}
