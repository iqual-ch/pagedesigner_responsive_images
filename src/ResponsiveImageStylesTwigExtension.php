<?php

namespace Drupal\pagedesigner_responsive_images;

use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Url;

/**
 * Extend Drupal's Twig_Extension class.
 */
class ResponsiveImageStylesTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('image_style', [$this, 'getImageStyles']),
      new \Twig_SimpleFunction('styled_image_url', [$this, 'getStyledImageUrl']),
    ];
  }

  /**
   * Returns associative array from $string.
   *
   * @param array|object $sizes
   *   JSON encoded component sizes. Can be of mulitple types.
   * @param string $template
   *   Image style template.
   *
   * @return array
   *   Array containing the image styles.
   */
  public function getImageStyles($sizes, string $template) {

    if (\is_array($sizes) && array_key_exists('#text', $sizes) && is_string($sizes['#text'])) {
      $sizes = json_decode($sizes['#text'], TRUE);
    }

    if (\is_object($sizes) && get_class($sizes) == "Drupal\Core\Render\Markup") {
      $sizes = json_decode($sizes->jsonSerialize(), TRUE);
    }

    if (\is_string($sizes)) {
      $sizes = json_decode($sizes, TRUE);
    }

    // $template = $template['#plain_text'];
    $config = \Drupal::entityTypeManager()->getStorage('image_style_template')->load($template);
    $settings = YamlParser::parse(YamlSerializer::decode($config->settings));
    $imageStyles = [];
    foreach ($settings as $breakpont => $templates) {
      $imageStyles[$breakpont] = [
        'templates' => $templates,
        'size'      => $sizes[$breakpont],
      ];
    }
    return $imageStyles;
  }

  /**
   * Return URL of a styled image.
   *
   * @param string $uri
   *   Image Uri.
   * @param string $styleName
   *   Image style.
   *
   * @return string
   *   URL to image after image style is applied.
   */
  public function getStyledImageUrl(string $uri, string $styleName = '') {
    if (empty($styleName)) {
      return file_create_url($uri);
    }
    $style = ImageStyle::load($styleName);
    return $style->buildUrl($uri);
  }

}
