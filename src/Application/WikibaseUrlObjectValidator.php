<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

use DataValues\StringValue;
use ValueValidators\ValueValidator;

class WikibaseUrlObjectValidator implements ObjectValidator {

	/**
	 * @param ValueValidator[] $validators
	 */
	public function __construct(
		private array $validators
	) {
	}

	public function isValid( string $object ): bool {
		$value = new StringValue( $object );
		foreach ( $this->validators as $validator ) {
			$result = $validator->validate( $value );
			if ( !$result->isValid() ) {
				return false;
			}
		}
		return true;
	}

}
