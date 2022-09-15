<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseRDF\Application;

interface ObjectValidator {

	public function isValid( string $object ): bool;

}
