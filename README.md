# Wikibase RDF

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/ProfessionalWiki/WikibaseRDF/ci.yml?branch=master)](https://github.com/ProfessionalWiki/WikibaseRDF/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF/coverage.svg)](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF)
[![Psalm level](https://shepherd.dev/github/ProfessionalWiki/WikibaseRDF/level.svg)](psalm.xml)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/wikibase-rdf/version.png)](https://packagist.org/packages/professional-wiki/wikibase-rdf)
[![Download count](https://poser.pugx.org/professional-wiki/wikibase-rdf/d/total.png)](https://packagist.org/packages/professional-wiki/wikibase-rdf)
[![License](https://img.shields.io/packagist/l/professional-wiki/wikibase-rdf)](LICENSE)

[Wikibase] extension that allows defining RDF mappings for Wikibase Entities.

[Professional.Wiki] created and maintains Wikibase RDF. We provide [Wikibase hosting], [Wikibase development] and [Wikibase consulting].

The [Wikibase Stakeholder Group] concieved and funded the extension.

**Table of Contents**

- [Usage](#usage)
  * [REST API](#rest-api)
- [Installation](#installation)
- [PHP Configuration](#php-configuration)
- [Development](#development)
- [Release notes](#release-notes)

## Usage

When the extension is enabled, Item and Property pages show a "Mapping to other ontologies" section.
This section is located in between the "In more languages" and "Statements" sections.

<img src="https://user-images.githubusercontent.com/146040/193851219-dc30080a-7cbb-4c1a-9800-e7c7d98ef644.png" style="border: 1px solid black" alt="Property page with a mapping">

Users with editing permissions can add, edit or remove mappings.

A mapping consists of a predicate and a URL. The predicate can only be one out of a preconfigured set of values. The URL has to be a valid URL.

<img src="https://user-images.githubusercontent.com/146040/193851211-b4031ca1-4cc9-47ab-9160-658f4a38d979.png" style="border: 1px solid black" alt="Mapping editing UI">

Mapping predicates can be configured via the `MediaWiki:MappingPredicates` page by anyone with interface-admin permissions.
You can also configure mapping predicates via [PHP Configuration](#php-configuration).

<img src="https://user-images.githubusercontent.com/146040/193851215-86b8ad05-0c1a-431c-ad4b-5750997fd642.png" style="border: 1px solid black" alt="Mapping predicates configuration page">

<img src="https://user-images.githubusercontent.com/146040/193854181-af8b85f2-1444-4882-a0af-d8123331f30c.png" style="border: 1px solid black" alt="Editing mapping predicates via the configuration page">

### REST API

This extension provides REST API endpoints for getting and setting the RDF mappings for a Wikibase entity.

For more information, refer to the [REST API documentation](docs/rest.md).

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

### Allowed predicates

List of allowed predicates.

Variable: `$wgWikibaseRdfPredicates`

Default: `[]`

Example:

```php
$wgWikibaseRdfPredicates = [
	'owl:sameAs',
	'owl:SymmetricProperty',
	'rdfs:subClassOf',
	'rdfs:subPropertyOf',
];
```

You can also configure what predicates are allowed via the `MediaWiki:MappingPredicates` page.

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

## Release notes

### Version 1.1.0 - 2022-11-25

* Added translations for various languages
* Added notification about SPARQL store behavior that shows on first edit

### Version 1.0.0 - 2022-10-04

Initial release for Wikibase 1.37 with these features:

* Ability to add mappings to Items and Properties via an on-page UI
* Inclusion of mappings in the RDF output
* Configurable relationships (predicates), including configuration UI on `MediaWiki:MappingPredicates`
* API endpoint to retrieve or update the mappings for an Entity
* API endpoint to retrieve all mappings defined on the wiki
* TranslateWiki integration
* Support for PHP 8.0 and 8.1

[Professional.Wiki]: https://professional.wiki
[Wikibase]: https://wikibase.consulting/what-is-wikibase/
[Wikibase hosting]: https://professional.wiki/en/hosting/wikibase
[Wikibase development]: https://www.wikibase.consulting/about-the-wikibase-team/
[Wikibase consulting]: https://wikibase.consulting/
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[LocalSettings.php]: https://www.pro.wiki/help/mediawiki-localsettings-php-guide
[Wikibase Stakeholder Group]:https://wbstakeholder.group/
