<?php

namespace Drupal\pagedesigner_responsive_images;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a listing of container configuration entities.
 *
 * @see \Drupal\pagedesigner_responsive_images\Entity\Container
 */
class ImageStyleTemplateListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    $header['id'] = t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => t('Edit Image Style Template'),
        'weight' => 20,
        'url' => $entity->urlInfo('edit-form'),
      ];
    }
    return $operations;
  }

}
