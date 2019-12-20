### Partners references query field
A field that lists the references developed by a partner.

#### Content type configuration
The "reference' content type with has a "partners" relation list field. Each partner involved in the reference is added.

The "partner" content type has a "references" content query field. It lists all references that include that partner.

##### Query type
AppBundle:RelatedToContent

##### Returned type
Reference

##### Parameters
```yaml
from_field: partners
sort_by: Name
content_type: '@=returnedType'
to_content: '@=content'
```

#### Layout customization
```yaml
ezpublish:
    systems:
        site:
            languages: [eng-GB]
            content_view:
                # Customize the layout around all the images
                content_query_field:
                    partner_references:
                        match:
                            Identifier\ContentType: partner
                            Identifier\FieldDefinition: references
                        template: "content/view/content_query_field/partner_references.html.twig"
                # Customize the layout of each image
                line:
                    reference:
                        match:
                            Identifier\ContentType: reference
                        template: "content/view/line/reference.html.twig"
```
