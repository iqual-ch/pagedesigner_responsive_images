<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'responsive_image_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "responsive_image_formatter",
 *   label = @Translation("Responsive imdage data"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ResponsiveImageFieldFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'responsive_image_template' => '',
      'responsive_image_component_sizes' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['responsive_image_template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image template'),
      '#default_value' => $this->getSetting('responsive_image_template'),
    ];

    $elements['responsive_image_component_sizes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Component sizes'),
      '#default_value' => $this->getSetting('responsive_image_component_sizes'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->getSetting('responsive_image_template');
    $summary[] = $this->getSetting('responsive_image_component_sizes');
    return $summary;
  }

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
