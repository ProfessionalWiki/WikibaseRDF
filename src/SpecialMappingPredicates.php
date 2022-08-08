<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use SpecialPage;

class SpecialMappingPredicates extends SpecialPage {

	public function __construct() {
		parent::__construct( 'MappingPredicates' );
	}

	public function execute( $subPage ): void {
		parent::execute( $subPage );

		$title = \Title::newFromText( 'MediaWiki:MappingPredicates' );

		if ( $title instanceof \Title ) {
			$this->getOutput()->redirect( $title->getFullURL() );
		}
	}

	public function getGroupName(): string {
		return 'wikibase';
	}

	public function getDescription(): string {
		return $this->msg( 'special-mapping-predicates' )->escaped();
	}

}
