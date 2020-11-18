<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner_image\Plugin\pagedesigner\Handler\Image;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;

/**
 * Process entities of type "image".
 *
 * @PagedesignerHandler(
 *   id = "responsive_image",
 *   name = @Translation("Image handler"),
 *   types = {
 *      "image",
 *      "img",
 *   },
 * )
 */
class ResponsiveImage extends Image {

  /**
   * {@inheritdoc}
   */
  public function get(Element $entity, string &$result = '') {
    if ($entity->field_media->entity != NULL && $entity->field_media->entity->field_media_image->entity != NULL) {
      $file = $entity->field_media->entity->field_media_image->entity;

      $imgUri = $file->getFileUri();

      $patternAdditionalDefinition = $this->getPatternDefinition($entity->parent->entity->field_pattern->value)->getAdditional();

      $templateField = $patternAdditionalDefinition['responsive_images']['template_fields'][$entity->field_placeholder->value];
      $sizesField = $patternAdditionalDefinition['responsive_images']['component_sizes_field'];

      if ($templateField && $sizesField) {
        // Hacky, needs rework.
        foreach ($entity->parent->entity->children as $item) {
          if ($item->entity->field_placeholder->value == $templateField) {
            $template = $item->entity->field_content->value;
          }

          if ($item->entity->field_placeholder->value == $sizesField) {
            $sizes = json_decode($item->entity->field_content->value, TRUE);
          }
        }

        if ($template) {
          $config = \Drupal::entityTypeManager()->getStorage('image_style_template')->load($template);
          $settings = YamlParser::parse(YamlSerializer::decode($config->settings));

          array_walk($settings, function (&$imageStyles, $breakpoint) use ($sizes, $imgUri) {
            $src = '';

            foreach ($imageStyles as $imageStyle => $width) {
              $style = \Drupal::entityTypeManager()
                ->getStorage('image_style')
                ->load($imageStyle);
              $src .= $style->buildUrl($imgUri) . ' ' . $width . ', ';
            }

            $imageStyles = [
              'srcset' => substr($src, 0, -2),
              'sizes' => $sizes[$breakpoint],
            ];
          });

          $output = [
            'img_original' => $file->createFileUrl(FALSE),
            'img_responsive' => $settings,
          ];

          if ($output && \json_encode($output)) {
            $result = \json_encode($output);
            return;
          }
        }
      }

      $result = $file->createFileUrl(FALSE);

    }
  }

}
