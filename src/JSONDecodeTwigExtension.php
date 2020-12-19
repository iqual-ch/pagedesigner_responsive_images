<?php

namespace Drupal\pagedesigner_responsive_images;

/**
 * Extend Drupal's Twig_Extension class.
 */
class JSONDecodeTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('json_decode', [$this, 'jsonDecode']),
    ];
  }

  /**
   * Returns associative array from $string.
   *
   * @param string $string
   *   JSON encoded string.
   *
   * @return array
   *   Decoded array
   */
  public function jsonDecode(...$args) {
    $string = json_decode($args[0], TRUE);
    return $string;
  }

}
