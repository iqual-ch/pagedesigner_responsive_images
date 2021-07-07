<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'responsive_image_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "responsive_image_formatter",
 *   label = @Translation("URI (for responsive image)"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ResponsiveImageFieldFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $image) {
      $imgUri = $image->getFileUri();
      $elements[$delta] = ['#markup' => $imgUri];
    }
    return $elements;
  }

}
