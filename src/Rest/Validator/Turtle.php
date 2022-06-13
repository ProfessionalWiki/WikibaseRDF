<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Rest\Validator;

use MediaWiki\Rest\RequestInterface;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Validator\BodyValidator;
use ProfessionalWiki\WikibaseRDF\Application\Mapping;
use ProfessionalWiki\WikibaseRDF\Application\MappingList;

class Turtle implements BodyValidator {

	/**
	 * @param string $contents the contents of the map.  We're expecting something like a predicate
	 *     list from the Turtle syntax.  "something like" because we do not do any strict parsing at
	 *     this point and semicolons or periods superfluous. commas are likewise ignored and would
	 *     actually result in incorrect parsing.
	 *
	 * @return array<string, string>
	 * @fixme right now this is a dumb processor that just expects text string with predicate and
	 * subjects on each line.  That is, it will read:
	 *
	 *       owl:sameAs owl:subClassOf ;
	 *       rdfs:subClassOf skos:exactMatch ;
	 *       random trash .
	 *
	 * and generate three array elements:
	 *
	 *       [ 'owl:sameAs' => 'owl:subClassOf' ]
	 *       [ 'rdfs:subClassOf' => 'skos:exactMatch' ]
	 *       [ 'random' => 'trash' ]
	 *
	 * that are then used by updateMapping to create map entries.  Ideally, this would use an RDF
	 * parser like EasyRDF or https://packagist.org/packages/pietercolpaert/hardf?
	 *
	 */
	protected function parse( string $contents ): array {
		$ret = [];
		foreach ( explode( "\r\n", $contents) as $lno => $line ) {
			$bit = explode( " ", $line, 3 );
			if ( count( $bit ) < 2 ) {
				throw new HttpException( "Insufficent terms on line $lno, $contents", 422 );
			}
			$ret[$bit[0]] = $bit[1];
		}
		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function validateBody( RequestInterface $request ) {
		$mapText = $request->getBody()->getContents();
		$map = [];
		foreach ( $this->parse( $mapText ) as $predicate => $object ) {
			$map[] = new Mapping( $predicate, $object );
		}
		return new MappingList( $map );
	}
}
