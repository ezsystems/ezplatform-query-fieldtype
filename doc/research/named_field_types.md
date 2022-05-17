# Named query field types

A higher level version of the query field type. Through configuration, queries are associated to a name. Those are added added to the list of available field types. When added, they query type isn't show, and the parameters are immediately displayed for editing. It saves time when modelling the content by allowing to reuse the same type for a similar concept.

## Examples

```
ezplatform:
  queries:
    children:
      type: eZ:Children
      default_parameters:
        location: '@=mainLocation'
        type: '@=returnedType'
    relating_content:
      type: eZ:ContentRelatedTo
      default_parameters:
        to_content: '@=content'
        type: '@=returnedType'
```

## Extra features

### Default query type parameters

Content and location level (not field) based parameters can get a default value: the current content, its section, the returned type...

### Translation

That extra layer is a good place for translating parameters.

### Customization

Custom templates could be associated to named query field types, giving extra flexibility.
It would allow to use or extend the same template when the same list type is used, without
template configuration.

### Extensibiliy

Named queries make it easy for 3rd parties to add their own field types without developing any:

- define new query types, with custom criteria if needed
- define named queries that would show up as field types, without implementing an actual field type
