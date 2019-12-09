# eZ Platform Query Field Type

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

Add the package to `app/AppKernel.php`:
```php
$bundles = [
  // ...
  new EzSystems\EzPlatformQueryFieldType\Symfony\EzSystemsEzPlatformQueryFieldTypeBundle(),
];
```

Add the bundle routes to `app/config/routing.yml`:
```yml
ezplatform.query_fieldtype.routes:
    resource: '@EzSystemsEzPlatformQueryFieldTypeBundle/Resources/config/routing/'
    type: directory
```

## Usage
Add a `query` field to a content type.

In the Field Definition settings, select a Query Type out of the ones defined in the system, as well as the content type
that is returned by that field.

Parameters are used to get the query type's parameters, on runtime, based on properties from the content item.
The syntax for it is YAML.. The key is the name of a query type parameter, and the value either a scalar, or an [expression](https://symfony.com/doc/current/components/expression_language.html).

A simple example, for a LocationChildren query type that expects:
- `parent_location_id`: id of the location to fetch children for
- `content_types`: content type identifier or array of identifiers to filter on

````yaml
parent_location_id: "@=content.contentInfo.mainLocationId"
content_types: "image"
````

See the [`examples`](doc/examples/) directory for full examples.
