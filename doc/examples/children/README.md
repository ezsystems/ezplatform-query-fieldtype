### Gallery images query field
A field that lists the children of a given type below the current location.

#### Content type configuration
The following assumes a "gallery" content type with an "images" query field.
The images are the sub-items of the gallery.


##### Query type
`AppBundle:Children` (defined in `ChildrenQueryType.php`)

##### Returned type
Image

##### Parameters
```yaml
parent_location_id: '@=mainLocation.id'
included_content_type_identifier: '@=returnedType'
```
