<?php

namespace Drupal\pagedesigner_responsive_images\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner_image\Plugin\pagedesigner\Handler\Image;
use Drupal\ui_patterns\Definition\PatternDefinitionField;

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
  public function collectAttachments(array &$attachments) {

  }

  /**
   * {@inheritdoc}
   */
  public function collectPatterns(array &$patterns) {

  }

  /**
   * {@inheritdoc}
   */
  public function adaptPatterns(array &$patterns) {

  }

  /**
   * {@inheritdoc}
   */
  public function prepare(PatternDefinitionField &$field, array &$fieldArray) {

  }

  /**
   * {@inheritdoc}
   */
  public function get(Element $entity, string &$result = '') {
    if ($entity->field_media->entity != NULL && $entity->field_media->entity->field_media_image->entity != NULL) {
      $file = $entity->field_media->entity->field_media_image->entity;
      $result = $file->getFileUri();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getContent(Element $entity, array &$list = [], $published = TRUE) {

  }

  /**
   * {@inheritdoc}
   */
  public function serialize(Element $entity, array &$result = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function describe(Element $entity, array &$result = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function generate($definition, array $data, Element &$entity = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function patch(Element $entity, array $data) {

  }

  /**
   * {@inheritdoc}
   */
  public function copy(Element $entity, Element $container = NULL, Element &$clone = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function delete(Element $entity, bool $remove = FALSE) {

  }

  /**
   * {@inheritdoc}
   */
  public function restore(Element $entity) {

  }

  /**
   * {@inheritdoc}
   */
  public function render(Element $entity, array &$build = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function renderForPublic(Element $entity, array &$build) {

  }

  /**
   * {@inheritdoc}
   */
  public function renderForEdit(Element $entity, array &$build = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function publish(Element $entity) {

  }

  /**
   * {@inheritdoc}
   */
  public function unpublish(Element $entity) {

  }

}
