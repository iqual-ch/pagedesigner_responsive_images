<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner_gallery\Plugin\pagedesigner\Handler\GalleryGallery;

/**
 * Process entities of type "gallery_gallery".
 *
 * @PagedesignerHandler(
 *   id = "responsive_image_gallery_gallery",
 *   name = @Translation("gallery handler"),
 *   types = {
 *      "gallery_gallery",
 *   },
 * )
 */
class ResponsiveImageGalleryGallery extends GalleryGallery {

  /**
   * Component sizes.
   *
   * @var sizes
   *   Stores the parent's size.
   */
  private $sizes = [];

  /**
   * Image style template.
   *
   * @var sizes
   *   Stores the parent's image stlye template.
   */
  private $template = NULL;

  /**
   * Sets the sizes & image style templates from the parent.
   */
  public function initResponsiveImages(array $sizes, string $template) {
    $this->sizes = $sizes;
    $this->template = $template;
  }

  /**
   * {@inheritdoc}
   */
  public function serialize(Element $entity, array &$result = []) {
    $data = [];
    if ($entity->children) {
      foreach ($entity->children as $item) {
        if ($item->entity != NULL) {
          if ($item->entity->field_media->entity != NULL && $item->entity->field_media->entity->field_media_image->entity != NULL) {
            $file = $item->entity->field_media->entity->field_media_image->entity;
            $url = $previewUrl = $file->createFileUrl(FALSE);
            $style = \Drupal::entityTypeManager()
              ->getStorage('image_style')
              ->load('pagedesigner_default');
            if ($style != NULL) {
              $url = $previewUrl = \Drupal::service('file_url_generator')->transformRelative($style->buildUrl($file->getFileUri()));
            }
            $style = \Drupal::entityTypeManager()
              ->getStorage('image_style')
              ->load('thumbnail');
            if ($style != NULL) {
              $previewUrl = \Drupal::service('file_url_generator')->transformRelative($style->buildUrl($file->getFileUri()));
            }

            $data[] = [
              'alt' => $item->entity->field_content->value,
              'id' => $item->entity->field_media->target_id,
              'src' => $url,
              'preview' => $previewUrl,
              'uri' => $file->getFileUri(),
            ];
          }
        }
      }
    }
    $result = $data + $result;
  }

  /**
   * {@inheritdoc}
   */
  public function get(Element $entity, string &$result = '') {
    $data = $this->elementHandler->serialize($entity);
    $stringData = [];
    foreach ($data as $item) {
      $stringData[] = $item['alt'];
    }
    return implode(' ', $stringData);
  }

}
