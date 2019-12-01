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
Add the package's repository to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/ezsystems/ezplatform-query-fieldtype.git"
    }
  ]
}
```

Add the package to the requirements:
```
composer require ezsystems/ezplatform-query-fieldtype:dev-master
```

Add the package to `app/AppKernel.php`:
```php
$bundles = [
  // ...
  new EzSystems\EzPlatformQueryFieldType\Symfony\EzSystemsEzPlatformQueryFieldTypeBundle(),
]
```

Add the bundle routes to `app/config/routing.yml`:
```yml
ezplatform.query_fieldtype.routes:
    resource: '@BDEzPlatformQueryFieldTypeBundle/Resources/config/routing/'
    type: directory
```

## Usage
Add a `query` field to a content type.

In the Field Definition settings, select a Query Type out of the ones defined in the system. Parameters are a JSON structure, with the key being the parameter's name, and the value either a scalar, or an [expression](https://symfony.com/doc/current/components/expression_language.html).

See the [`examples`](examples/) directory for full examples.



