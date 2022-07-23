# Wikibase RDF

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/ProfessionalWiki/WikibaseRDF/CI)](https://github.com/ProfessionalWiki/WikibaseRDF/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF)
[![Psalm level](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF/level.svg)](psalm.xml)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/wikibase-rdf/version.png)](https://packagist.org/packages/professional-wiki/wikibase-rdf)
[![Download count](https://poser.pugx.org/professional-wiki/wikibase-rdf/d/total.png)](https://packagist.org/packages/professional-wiki/wikibase-rdf)
[![License](https://img.shields.io/packagist/l/professional-wiki/wikibase-rdf)](LICENSE)

[Wikibase] extension that allows defining RDF mappings for Wikibase Entities.

Wikibase RDF has been created and is maintained by [Professional.Wiki].
It was conceived and funded by the [Wikibase Stakeholder Group].

**Table of Contents**

- [Usage](#usage)
- [Installation](#installation)
- [PHP Configuration](#php-configuration)
- [Development](#development)
- [Release notes](#release-notes)

## Usage

TODO

## Installation

Platform requirements:

* [PHP] 8.0 or later (tested up to 8.1)
* [MediaWiki] 1.37 or later (tested up to 1.37)
* [Wikibase] 1.37 or later (tested up to 1.37)

The recommended way to install Wikibase RDF is using [Composer] with
[MediaWiki's built-in support for Composer][Composer install].

On the commandline, go to your wikis root directory. Then run these two commands:

```shell script
COMPOSER=composer.local.json composer require --no-update professional-wiki/wikibase-rdf:~1.0
```
```shell script
composer update professional-wiki/wikibase-rdf --no-dev -o
```

Then enable the extension by adding the following to the bottom of your wikis [LocalSettings.php] file:

```php
wfLoadExtension( 'WikibaseRDF' );
```

You can verify the extension was enabled successfully by opening your wikis Special:Version page in your browser.

## PHP Configuration

Configuration can be changed via [LocalSettings.php].

TODO

## Development

To ensure the dev dependencies get installed, have this in your `composer.local.json`:

```json
{
	"require": {
		"vimeo/psalm": "^4",
		"phpstan/phpstan": "^1.4.9"
	},
	"extra": {
		"merge-plugin": {
			"include": [
				"extensions/WikibaseRDF/composer.json"
			]
		}
	}
}
```

### Running tests and CI checks

You can use the `Makefile` by running make commands in the `WikibaseRDF` directory.

* `make ci`: Run everything
* `make test`: Run all tests
* `make cs`: Run all style checks and static analysis

Alternatively, you can execute commands from the MediaWiki root directory:

* PHPUnit: `php tests/phpunit/phpunit.php -c extensions/WikibaseRDF/`
* Style checks: `vendor/bin/phpcs -p -s --standard=extensions/WikibaseRDF/phpcs.xml`
* PHPStan: `vendor/bin/phpstan analyse --configuration=extensions/WikibaseRDF/phpstan.neon --memory-limit=2G`
* Psalm: `php vendor/bin/psalm --config=extensions/WikibaseRDF/psalm.xml`

### REST API

Save all RDF mappings for an existing entity (e.g. Q1):

```shell
curl -X PUT -H 'Content-Type: application/json' "http://localhost:8484/rest.php/wikibase-rdf/v0/mappings/Q1" \
  -d '[{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"}, {"predicate": "owl:sameAs", "object": "owl:subClassOf"}, {"predicate": "foo:bar", "object": "http://example.com"}]'
```

Retrieve all RDF mappings for an existing entity (e.g. Q1):

```shell
curl "http://localhost:8484/rest.php/wikibase-rdf/v0/mappings/Q1"
```

## Release notes

### Version 1.0.0 - TBD

Initial release for Wikibase 1.37 with these features:

* Ability to add mappings to Items and Properties via an on-page UI
* Inclusion of mappings in the RDF output
* Configurable relationships (predicates)
* API endpoint to retrieve or update the mappings for an Entity
* TranslateWiki integration
* Support for PHP 8.0 and 8.1

[Professional.Wiki]: https://professional.wiki
[Wikibase]: https://wikibase.consulting/what-is-wikibase/
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[LocalSettings.php]: https://www.mediawiki.org/wiki/Manual:LocalSettings.php
[JSON Schema]: https://github.com/ProfessionalWiki/WikibaseRDF/blob/master/schema.json
[Wikibase Stakeholder Group]:https://wbstakeholder.group/
