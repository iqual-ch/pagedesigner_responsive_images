<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;

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
    $template = $this->getSetting('responsive_image_template');
    $sizes = json_decode($this->getSetting('responsive_image_component_sizes'), TRUE);

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $image) {

      $imgUri = $image->getFileUri();
      $result = file_create_url($imgUri);
      $result = file_url_transform_relative($result);

      if ($template) {
        $config = \Drupal::entityTypeManager()->getStorage('image_style_template')->load($template);
        if ($config) {
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
            'img_original' => $result,
            'img_responsive' => $settings,
          ];

          if ($output && \json_encode($output)) {
            $result = \json_encode($output);
          }
        }
      }

      $elements[$delta] = ['#markup' => $result];
    }

    return $elements;
  }

}
