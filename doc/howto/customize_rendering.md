# How-to: customize rendering

In this how-to, you will learn how to customize all the rendering phases.

## Rendering process
Content query fields are rendered using `ez_render_field()`. 
The query is executed, the items iterated on, and each is rendered using the `line` content view.

That template renders the content, the view controller, with a custom view type (`content_query_view`). A custom view
builder executes execute the query, and assigns the results to the view as `items`. The default template for that view (`query_field_view.html.twig`) iterates on each item resulting from the query, and renders each with the `line` view.

The field renderer for a query field supports the following parameters:
- `bool enablePagination`: force pagination enabled, even if it is disabled for that field definition
- `bool disablePagination`: force pagination disabled, even if it is disabled for that field definition
- `int itemsPerPage`: sets how many items are displayed per page with pagination. Required if `enablePagination` is
  used and pagination is disabled in the field definition

### Summary
1. Your template: `ez_render_field(content, 'queryfield')`
2. Field view template: `fieldtype_ui.html.twig`:
3. Render the content with the `content_query_view` type: `render( controller( "ez_content:viewAction" ) )`
4. The View Builder executes the query and assigns the results to the `items` iterable
4. The `content_query_view` default template: `query_field_view.html.twig`
5. The view template renders each content item with the `line` view: `render (controller( "ez_content:viewAction", {viewType: 'line'} ) )`

## Customizing the field view template
To customize how the items list container is rendered, you need to create a custom view template.

To do so, create a view configuration rule for the `content_query_view` type, and use the `Identifier\FieldDefinition`
matcher to match your query field definition.

Example that sets a custom template to render the `images` field of the `gallery` content type:
```yaml
ezplatform:
    system:
        default:
            content_view:
                content_query_field:
                    gallery_images:
                        match:
                            Identifier\ContentType: gallery
                            Identifier\FieldDefinition: images
                        template: "content/view/gallery_images.html.twig"                    
```

As with any content view, a custom controller can also be defined to further customize the view.

Use the `items` iterable to loop over the field's content items:
```
<div class="my-list">
{% for item in items %}
    {{ render(controller("ez_content:viewAction", {
        "contentId": item.id,
        "content": item,
        "viewType": itemViewType
    })) }}
{% endfor %}
</div>

{% if isPaginationEnabled %}
    {{ pagerfanta( items, 'ez', {'routeName': location, 'pageParameter': pageParameter } ) }}
{% endif %}
```

In addition to the [usual content view templates variables](https://doc.ezplatform.com/en/latest/api/field_type_form_and_template/#template-variables), the following variables are available:
- `Content content`: the item that contains the query field.
- `bool isPaginationEnabled`: indicates if pagination is enabled. When it is, `items` is a `PagerFanta` instance.
- `string pageParameter`: when pagination is enabled, contains the page parameter to use as the pager fanta
  `pageParameter` argument (important, as it makes every pager unique, required if there are several query
  fields in the same item)

## Customizing the line view template
The line view template, used to render each result, can be customized by creating `line` view configuration rules.

In the example above, gallery images, each image content item will be rendered using the `line` view.  
THat examples defines a custom template for `image` content items returned by `gallery.images` query field:
```yaml
ezplatform:
    system:
        default:
            content_view:
                line:
                    image:
                        match:
                            Identifier\ContentType: image
                        template: "path/to/template.html.twig"                    
```

The variables are the same than other view template ([documentation]((https://doc.ezplatform.com/en/latest/api/field_type_form_and_template/#template-variables))). 

## Advanced

### Changing the default view types
Both view types used for rendering can be changed globally. To override them, define the following parameters in your
project's config:

```yaml
parameters:
    ezcontentquery_field_view: 'my_content_query_field'
    ezcontentquery_item_view: 'my_line'
```  
