<?php

namespace Drupal\pagedesigner_responsive_images\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the container configuration entity.
 *
 * @ConfigEntityType(
 *   id = "image_style_template",
 *   label = @Translation("Image style template"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "form" = {
 *       "default" = "Drupal\pagedesigner_responsive_images\Form\ImageStyleTemplateForm",
 *       "delete" = "Drupal\pagedesigner_responsive_images\Form\ImageStyleTemplateDeleteForm"
 *     },
 *     "list_builder" = "Drupal\pagedesigner_responsive_images\ImageStyleTemplateListBuilder"
 *   },
 *   admin_permission = "administer image style templates",
 *   config_prefix = "image_style_template",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "settings" = "settings",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "settings",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/pagedesigner/responsive-images/image-style-templates/add",
 *     "edit-form" = "/admin/config/pagedesigner/responsive-images/image-style-templates/{image_style_template}/edit",
 *     "delete-form" = "/admin/config/pagedesigner/responsive-images/image-style-templates/{image_style_template}/delete",
 *   }
 * )
 */
class ImageStyleTemplate extends ConfigEntityBase {

  /**
   * Template machine name.
   *
   * @var string
   */
  public $id;

  /**
   * Template human readable name.
   *
   * @var string
   */
  public $label;

}
