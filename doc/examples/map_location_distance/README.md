### Nearby places query field
A field that returns items based on their distance relative to the current item.

#### Content type configuration
The following assumes a "place" content item with a "location" map location
field definition.

##### Query type
"RelativeDistance" (see [NearbyPlacesQueryType](NearbyPlacesQueryType.php).

##### Parameters
```yaml
distance: 3
latitude: "@=content.getFieldValue('location').latitude"
longitude": "@=content.getFieldValue('location').longitude"
```
