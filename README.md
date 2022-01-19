
# Pagedesigner responsive images

Module that adds support for responsive images to pagedesigner components. It provides all the information needed to create a `<picture>` element that contains multiple images for different viewport sizes, as well as multiple resolutions of each image.

There's a submodule *pagedesigner_focal_point* that enables focal point based cropping for images


## Concept / How does it work?

At the end of the day, a rendered responsive image should look something like this:

    <picture>
      <source media="(min-width: 1200px)" sizes="350px" srcset="/path/to/x-large.img 2000w,/path/to/large.img 1000w">
      <source media="(min-width: 768px)" sizes="50vw" srcset="/path/to/medium.img 600w">
      <source media="(min-width: 300px)" sizes="100vw" srcset="/path/to/medium.img 100w">
      <img src="/path/to/fallback.img" loading="lazy" >
    </picture>

There are 3 critical attributes inside the `<sources>` tag:
- **scrsct**
The source set defines the URLs to different images and their sizes. Drupal's image styles are used to generate all all the images and get their URLs. For the handling of image styles, this module introduces *image style templates* which are collections of image styles. An image style template maps different viewport sizes to different sets of image styles.

- **sizes**
The width of the image when it's rendered. Inside pagedesigner, the compontent's will be calculated based on it's parents and their dimensions. Since this is strongly depending on the theme implementation, it is possible to configure of rules that map component withs to CSS selectors per viewport.

- **media**
A media condition that need to be fullfilled for the source to be showed.


The module provides 2 new pagedesigner fields for responsive image handling:
- **ImageStyleTemplate**
Provides a selection field to choose from the defined image style templates.

- **ComponentSizes**
Automatically calculates the size of the component based on its parents.

The **ResponsiveImageHandler** – an extension to the standard image handler – then returns the image's URI which is used to apply the image styles.

## Usage
### Basic usage with iq_barrio-based themes
The image and gallery patterns already have the ImageStyleTemplate & ComponentSizes fields implemented, therefore they're ready to be used.

**Add responsive images to a custom pattern**
Add image style template and component fields to pattern definition.

    img:
      fields:
        image_img:
          type: image
          label: The image
        image_template:
          type: image_style_template
          label: Template
        sizes:
          type: component_sizes
          label: Size

Add responsive image definition to pattern:

    responsive_images:
      template_fields:  // mapping of image fields to template fields
        image_img: image_template  // image field > image template field
      component_sizes_field: sizes // name of sizes field

Use the following twig code for the image (include, provided by iq_barrio)

    {% include '@iq_barrio/includes/responsive-image.html.twig' with {
      'image' : image_img,
      'template' : image_template,
      'sizes': sizes,
        'attributes' : {
          ... additional attributes
        }
     } %}


**Add responsive images to a custom template**
Use the following twig code for the image (include, provided by iq_barrio)

    {% include '@iq_barrio/includes/responsive-image.html.twig' with {
      'image' : image.entity.field_media_image.entity.uri.value,    // URI of the image
      'template' : 'image_standard',                                // Machine name of image style template
      'sizes': {'1200px':'350px','768px':'50vw','300px':'100vw'},   // The size of the rendered image, per breakpoint. 
      'attributes' : {
        ... additional attributes                                   // Additional attributes for the <img> tag
      }
     } %}

### Advanced usage with non-iq_barrio-based themes
Run! Or use this Twig code instead of the one above:

    {#
      image: URI to image
      template: Machine name of image style template
      sizes: Associative array containing the sizes
    #}

    {% if sizes is not empty and template|render is not empty %}
      <picture>
        {% for key, value in image_style(sizes, template) %}
          <source media="(min-width: {{ key }})" sizes="{{ value.size }}" srcset="{% for image_style_name, size in value.templates %}{{ styled_image_url(image|render, image_style_name) }} {{ size }}{% if not loop.last %},{% endif %}{% endfor %}" ></source>
        {% endfor %}
        <img src="{{file_url(image)}}" ... additional attributes />
      </picture>
    {% endif %}


## Advanced

### Define image style templates

Define the image templates at the following link:
*/admin/config/pagedesigner/responsive-images/image-style-templates*

Use YAML syntax as follows:

    viewport width:
      image style machine name: image width after style is applied
example:

    1200px:
      pdri_1920_400__2000: 2000w
      pdri_1920_400__1000: 1000w
      pdri_1920_400__500: 500w
      pdri_1920_400__200: 200w
      pdri_1920_400__100: 100w
    768px:
      pdri_1920_400__1000: 1000w
      pdri_1920_400__500: 500w
      pdri_1920_400__200: 200w
      pdri_1920_400__100: 100w
    300px:
      pdri_3_1__500: 500w
      pdri_3_1__200: 200w
      pdri_3_1__100: 100w

### Define rules for component size calculation
Define the rules at the following link:
*/admin/config/pagedesigner/responsive-images*

Use YAML syntax as follows:

    .iq-row[class*="fullwidth"]:
      1200px: 100vw
      768px: 100vw
      300px: 100vw
    .iq-row:
      1200px: min( 100vw, 1140px )
      768px: 100vw
      300px: 100vw
    .col-md-1:
      1200px: 0.8333333
      768px: 0.8333333
      300px: 1
    .col-md-2:
      1200px: 0.16666667
      768px: 0.16666667
      300px: 1

The calculation then works as follows: If a value for a breakpoint is numeric, it will be multiplied with the parent's size. Otherwise the the mininium of both values is calculated.

Example:

    <div class="iq-row">
      <div class="col-md-1">
        <img class="iq-image"/>
      </div>
    </div>

The `iq-image` components size would then be: `calc( 0.8333333 * min( 100vw, 1140px ) )`
