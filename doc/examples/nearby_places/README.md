### Nearby places query field

A field that lists Place items located close to the current item.

#### Content type configuration
The following assumes a "place" content item with a "location" map location
field definition.

##### Query type
"Nearby places" (see [NearbyPlacesQueryType](NearbyPlacesQueryType.php).

##### Parameters
```json
{
  "distance": 3,
  "latitude": "@=content.getFieldValue('location').latitude",
  "longitude": "@=content.getFieldValue('location').longitude",
}
```
