<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\DataAccess;

use ProfessionalWiki\WikibaseRDF\Application\PredicateList;

class WikiMappingPredicatesLookup implements MappingPredicatesLookup {

	public function __construct(
		private PageContentFetcher $contentFetcher,
		private PredicatesDeserializer $deserializer,
		private string $pageName
	) {
	}

	public function getMappingPredicates(): PredicateList {
		$content = $this->contentFetcher->getPageContent( 'MediaWiki:' . $this->pageName );

		if ( $content instanceof \TextContent ) {
			return $this->predicatesFromTextContent( $content );
		}

		return new PredicateList();
	}

	private function predicatesFromTextContent( \TextContent $content ): PredicateList {
		return $this->deserializer->deserialize( $content->getText() );
	}

}
