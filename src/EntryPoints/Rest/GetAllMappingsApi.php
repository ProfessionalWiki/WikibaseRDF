<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\EntryPoints\Rest;

use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\WikibaseRDF\WikibaseRdfExtension;
use stdClass;

class GetAllMappingsApi extends SimpleHandler {

	/**
	 * @psalm-suppress all
	 * @return array<string, mixed>
	 */
	public function run(): array {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		$res = $dbr->newSelectQueryBuilder()
			->select( [
				// Actual fields
				'text.old_text',
				'page.page_title',
				// TODO: debug fields
				'page.page_id',
				'text.old_id',
			] )
			->from( 'text' )
			->join( 'page', conds: 'text.old_id=page.page_latest' )
			->join( 'slots', conds: 'text.old_id=slots.slot_content_id' )
			->join( 'slot_roles', conds: 'slots.slot_role_id=slot_roles.role_id' )
			->where( [ 'slot_roles.role_name' => WikibaseRdfExtension::SLOT_NAME ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$rows = []; // TODO: debug
		$mappings = [];
		/** @var stdClass $row */
		foreach ( $res as $row ) {
			$rows[] = $row; // TODO: debug
			$mappings[$row->page_title] ??= [];
			$mappings[$row->page_title][] = (array)json_decode( $row->old_text, true )
				// TODO: debug fields
				+ [ 'page_id' => $row->page_id, 'revision' => $row->old_id ];
		}

		return [
//			 'rows' => $rows, // TOOD: debug
			'mappings' => $mappings,
		];
	}

}
