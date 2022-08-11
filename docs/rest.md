# Wikibase RDF REST API

**Table of Contents**

- [Get Entity Mappings](#get-entity-mappings)
- [Save Entity Mappings](#save-entity-mappings)
- [Get All Mappings](#get-all-mappings)

## Get Entity Mappings

Gets the mappings for a specific Wikibase entity.

Route: `/wikibase-rdf/v0/mappings/{entity_id}`

Method: `GET`

Example for retrieving all RDF mappings for entity `Item:Q1`:

```shell
curl "http://localhost:8484/rest.php/wikibase-rdf/v0/mappings/Q1"
```

### Request parameters

**Query**

| parameter   | required | example                                       | description                                 |
|-------------|----------|-----------------------------------------------|---------------------------------------------|
| `entity_id` | yes      | `Q1` for `Item:Q1`<br/>`P1` for `Property:P1` | The entity ID, without the namespace prefix |

**Body**

None

### Responses

#### 200: Success

Mappings were retrieved or no mappings were found.

**Example with mappings**

```json
{
  "mappings": [
    {
      "predicate": "owl:sameAs",
      "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"
    },
    {
      "predicate": "rdfs:subClassOf",
      "object": "foo:Bar"
    }
  ]
}
```

**Example without mappings**

```json
{
  "mappings": []
}
```

#### 400: Invalid Entity ID

The Entity ID format is invalid.

**Example with invalid Entity ID `ABC123`**

```json
{
  "messageTranslations": {
    "en": "The entity ID you specified is invalid"
  },
  "httpCode": 400,
  "httpReason": "Bad Request"
}
```

### Response schema

| key                     | type   | description               |
|-------------------------|--------|---------------------------|
| `mappings`              | array  | List of mapping objects   |
| `mappings[i].predicate` | string | Predicate for mapping `i` |
| `mappings[i].object`    | string | Object for mapping `i`    |

## Save Entity Mappings

Sets the mappings for a specific Wikibase entity.

Route: `/wikibase-rdf/v0/mappings/{entity_id}`

Method: `POST`

Example for saving all RDF mappings for existing entity `Item:Q1`:

```shell
curl -X POST -H 'Content-Type: application/json' "http://localhost:8484/rest.php/wikibase-rdf/v0/mappings/Q1" \
  -d '[{"predicate": "owl:sameAs", "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"}, {"predicate": "rdfs:subClassOf", "object": "foo:Bar"}]'
```

### Request parameters

**Query**

| parameter   | required | example                                       | description                                 |
|-------------|----------|-----------------------------------------------|---------------------------------------------|
| `entity_id` | yes      | `Q1` for `Item:Q1`<br/>`P1` for `Property:P1` | The entity ID, without the namespace prefix |

**Body**

The request body must be a JSON array with each item containing:

| key             | type   | description               |
|-----------------|--------|---------------------------|
| `[i].predicate` | string | Predicate for mapping `i` |
| `[i].object`    | string | Object for mapping `i`    |

### Response

#### 200: Success

A successful save will have an empty response body.

#### 400: Invalid Entity ID

```json
{
  "messageTranslations": {
    "en": "The entity ID you specified is invalid"
  },
  "httpCode": 400,
  "httpReason": "Bad Request"
}
```

#### 400: Invalid Mappings

Example containing an incomplete mapping:

```json
{
  "invalidMappings": [
    {
      "predicate": "rdfs:subClassOf",
      "object": ""
    }
  ],
  "messageTranslations": {
    "en": "Some of the mappings you specified are invalid"
  },
  "httpCode": 400,
  "httpReason": "Bad Request"
}
```

#### 403: Permission Denied

```json
{
  "messageTranslations": {
    "en": "You do not have permission to edit this entity's mappings"
  },
  "httpCode": 403,
  "httpReason": "Forbidden"
}
```

#### 500: Save Failed

Indicates a failure unrelated to the request data.

```json
{
  "messageTranslations": {
    "en": "Save failed"
  },
  "httpCode": 500,
  "httpReason": "Internal Server Error"
}
```

### Response schema

None for successful requests.

Requests containing invalid mappings will respond with the invalid mappings:

| key                            | type   | description                         |
|--------------------------------|--------|-------------------------------------|
| `invalidMappings`              | array  | List of invalid mappings            |
| `invalidMappings[i].predicate` | string | Predicate attempted for mapping `i` |
| `invalidMappings[i].object`    | string | Object attempted for mapping `i`    |

## Get All Mappings

Gets the mappings for all Wikibase entities.

Route: `/wikibase-rdf/v0/mappings`

Method: `GET`

Example for retrieving all RDF mappings for all entities:

```shell
curl "http://localhost:8484/rest.php/wikibase-rdf/v0/mappings"
```

### Request parameters

None

### Responses

#### 200: Success

Mappings were retrieved or no mappings were found.

```json
{
  "mappings": {
    "Q1": [
      {
        "predicate": "owl:sameAs",
        "object": "http://www.w3.org/2000/01/rdf-schema#subClassOf"
      },
      {
        "predicate": "rdfs:subClassOf",
        "object": "foo:Bar"
      }
    ],
    "Q4": [
      {
        "predicate": "owl:sameAs",
        "object": "bar:Baz"
      }
    ],
    "P3": [
      {
        "predicate": "rdfs:subPropertyOf",
        "object": "http://www.w3.org/2000/01/rdf-schema#label"
      },
      {
        "predicate": "foo:Bar",
        "object": "bar:Baz"
      }
    ]
  }
}
```

### Response schema

| key                        | type   | description                             |
|----------------------------|--------|-----------------------------------------|
| `mappings`                 | array  | List of entities                        |
| `mappings[n]`              | array  | List of mapping objects per entity      |
| `mappings[n][i].predicate` | string | Predicate for mapping `i` of entity `n` |
| `mappings[n][i].object`    | string | Object for mapping `i` of entity `n`    |
