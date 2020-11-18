<?php

namespace Drupal\pagedesigner_responsive_images\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete an Image Style Template.
 */
class ImageStyleTemplateDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->messenger()->addMessage($this->t('Template %label has been deleted.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.image_style_template.collection');
  }

}
