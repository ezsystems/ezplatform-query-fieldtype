# Ibexa Query Field Type

This Field Type will let a content manager map an executable Repository Query to a Field.

Example use-cases:
- a `place.nearby_places` field that returns Place items less than X kilometers away
  from the current content, based on its own `location` field
- a `gallery.images` field that returns Image items that are children of the current
  gallery item's main location

The idea is to move content and structure logic implemented in controllers and templates
to the repository itself.

## Installation
Add the package to the requirements:
```
composer require ezsystems/ezplatform-query-fieldtype:^1.0@dev
```

Enable the bundle:
```php
// config/bundles.php

return [
  // ...
  EzSystems\EzPlatformQueryFieldType\Symfony\EzSystemsEzPlatformQueryFieldTypeBundle::class => ['all' => true],
  // ..
];
```

Add the bundle routes to `config/routing.yaml`:
```yml
ezplatform.query_fieldtype.routes:
    resource: '@EzSystemsEzPlatformQueryFieldTypeBundle/Resources/config/routing/'
    type: directory
```

## Usage
Add a Content query field to a content type.

In the Field Definition settings, select a Query Type from the list, as well as the content type that is returned by that field.

Parameters are used to build the query on runtime. They are either static, or mapped to properties from the content
the field value belongs to. The syntax YAML, with the key being the name of a query type parameter, and the value
either a scalar, or an [expression](https://symfony.com/doc/current/components/expression_language.html).

The following variables are available for use in expressions:
- `string returnedType`: the identifier of the content type that was previously selected
- `eZ\Publish\API\Values\Content\Content content`: the current content item
  Also gives you access to fields values. Example with an `ezurl` field: `@=content.getFieldValue('url').link`
- `eZ\Publish\API\Values\Content\ContentInfo contentInfo`: the current content item's content info
- `eZ\Publish\API\Values\Content\Location mainLocation`: the current content item's main location

A simple example, for a LocationChildren query type that expects:
- `parent_location_id`: id of the location to fetch children for
- `content_types`: content type identifier or array of identifiers to filter on

````yaml
parent_location_id: "@=mainLocation.id"
content_types: "@=returnedType"
````

See the [`examples`](doc/examples/) directory for full examples.

## COPYRIGHT
Copyright (C) 1999-2021 Ibexa AS (formerly eZ Systems AS). All rights reserved.

## LICENSE
This source code is available separately under the following licenses:

A - Ibexa Business Use License Agreement (Ibexa BUL),
version 2.4 or later versions (as license terms may be updated from time to time)
Ibexa BUL is granted by having a valid Ibexa DXP (formerly eZ Platform Enterprise) subscription,
as described at: https://www.ibexa.co/product
For the full Ibexa BUL license text, please see:
https://www.ibexa.co/software-information/licenses-and-agreements (latest version applies)

AND

B - GNU General Public License, version 2
Grants an copyleft open source license with ABSOLUTELY NO WARRANTY. For the full GPL license text, please see:
https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
