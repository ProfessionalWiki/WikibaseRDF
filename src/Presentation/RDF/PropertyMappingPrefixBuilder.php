<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Presentation\RDF;

use Wikibase\Repo\Rdf\RdfVocabulary;

class PropertyMappingPrefixBuilder {

	public function __construct(
		private string $rdfNodeNamespacePrefix
	) {
	}

	public function getPrefix(): string {
		return $this->rdfNodeNamespacePrefix . RdfVocabulary::NSP_DIRECT_CLAIM;
	}

}
