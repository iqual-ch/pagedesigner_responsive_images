<?php

namespace Drupal\pagedesigner_responsive_images\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Alter image style paths to accept slashes in params.
 */
class ImageStylePathProcessor implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {

    if (strpos($path, '/sites/default/files/styles/') === 0) {

      if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $request->getPathInfo())) {
        $pathParts = explode('/', str_replace('/sites/default/files/styles/', '', $request->getPathInfo()));
        $imageStyle = $pathParts[0];
        array_shift($pathParts);
        $uriBase = array_shift($pathParts);
        $imgUri = $uriBase . '://' . rawurldecode(implode('/', $pathParts));

        $style = \Drupal::entityTypeManager()
          ->getStorage('image_style')
          ->load($imageStyle);

        if ($style) {
          $imgUrl = $style->buildUrl($imgUri);
          $response = new RedirectResponse($imgUrl);
          $response->send();
        }
      }
    }
    return $path;
  }

}
