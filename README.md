# Pagedesigner responsive images

Module that adds support for responsive images to page designer components. It provides all the information needed to create a `<picture>` element that contains multiple images for different viewport sizes, as well as multiple resolutions of each image.


## Concept / How does it work?

The module introduces image style templates which are collections of image styles. An image style template maps different viewport sizes to different sets of image styles.

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


Once the image style templates are defined, use the 2 new pagedesigner fields provided by the module:

**ImageStyleTemplate**
Provides a selection field to choose from the defined image style templates.

**ComponentSizes**
Automatically calculates the size of the component based on its parents.

The **ResponsiveImageHandler** – an exception to the standard image handler – then combines the ImageStyleTemplate & ComponentSizes fields to provide all the data needed to build the image tag.


## Add functionality to pattern

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

Write twig file for pattern

    {% set fallback = "" %}
    <picture>
      {% for breakpoint, image_data in img['img_responsive'] %}
        {% set fallback = img['img_responsive'][breakpoint].srcset %}
        <source media="(min-width: {{breakpoint}})"
                srcset="{{fallback}}"
                sizes="{{img['img_responsive'][breakpoint].sizes}}"  />
      {% endfor %}
      <img src="{{img['img_original']}}"
           loading="lazy"
           title="{{image_title}}"
           alt="{{image_alt}}"  />
    </picture>
