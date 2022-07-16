<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF;

use EditPage;
use OutputPage;
use Skin;

class Hooks {

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		if ( $out->getTitle()->inNamespaces( WB_NS_ITEM, WB_NS_PROPERTY ) ) {
			$out->addModules( 'ext.wikibase.rdf' );
		}
	}

}
