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
      $result = $file->getFileUri();
    }
  }

}
