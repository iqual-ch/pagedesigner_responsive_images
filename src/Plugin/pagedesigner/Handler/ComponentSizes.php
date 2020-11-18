<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\pagedesigner\Handler;

use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;
use Drupal\pagedesigner\Plugin\pagedesigner\Handler\Longtext;

/**
 * Process entities of type "component_sizes".
 *
 * @PagedesignerHandler(
 *   id = "component_sizes",
 *   name = @Translation("Image handler"),
 *   types = {
 *     "component_sizes",
 *   },
 * )
 */
class ComponentSizes extends Longtext {

  /**
   * {@inheritdoc}
   */
  public function collectAttachments(array &$attachments) {
    // $attachments['library'][] = 'pagedesigner_image/pagedesigner';
    $attachments['library'][] = 'pagedesigner_responsive_images/pagedesigner';

    $config = \Drupal::config('pagedesigner_responsive_images.settings');
    $attachments['drupalSettings']['pagedesigner_responsive_images']['sizes'] = YamlParser::parse(YamlSerializer::decode($config->get('grid_sizes')));

  }

}
