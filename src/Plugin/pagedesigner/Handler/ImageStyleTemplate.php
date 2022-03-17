<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\pagedesigner\Handler;

use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;
use Drupal\pagedesigner\Plugin\pagedesigner\Handler\Select;

/**
 * Process entities of type "image_style_template".
 *
 * @PagedesignerHandler(
 *   id = "image_style_template",
 *   name = @Translation("Image handler"),
 *   types = {
 *      "image_style_template",
 *   },
 * )
 */
class ImageStyleTemplate extends Select {

  /**
   * {@inheritdoc}
   */
  public function collectAttachments(array &$attachments) {
    $attachments['library'][] = 'pagedesigner_responsive_images/pagedesigner';

    $storage = \Drupal::entityTypeManager()->getStorage('image_style_template');
    $list = [];
    foreach ($storage->loadMultiple() as $id => $entity) {
      $list[$id] = [
        'label' => $entity->label(),
        'settings' => YamlParser::parse(YamlSerializer::decode($entity->settings)),
      ];
    }

    $attachments['drupalSettings']['pagedesigner_responsive_images']['image_style_templates'] = $list;

  }

}
