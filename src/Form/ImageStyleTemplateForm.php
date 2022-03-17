<?php

namespace Drupal\pagedesigner_responsive_images\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;

/**
 * Defines the image style template form.
 */
class ImageStyleTemplateForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $template = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => 'Label',
      '#default_value' => $template->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $template->id(),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'templateExists'],
        'replace_pattern' => '[^a-z0-9_.]+',
      ],
    ];

    if ($template->id()) {
      $form['id']['#disabled'] = TRUE;
    }

    $form['settings'] = [
      '#type' => 'textarea',
      '#title' => 'Settings',
      '#default_value' => Yaml::decode($template->get('settings')),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $template = $this->entity;
    $template->set('settings', Yaml::encode($template->get('settings')));
    $status = $template->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Template %label created.', [
        '%label' => $template->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Template %label updated.', [
        '%label' => $template->label(),
      ]));
    }

    // Redirect back to the list view.
    $form_state->setRedirect('entity.image_style_template.collection');

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    try {
      Yaml::encode($form_state->getValue('settings'));
    }
    catch (InvalidDataTypeException $e) {
      $form_state->setErrorByName(
        'settings',
        $this->t('The provided configuration is not a valid yaml text.')
      );
    }
  }

  /**
   * Checks if a template machine name is taken.
   *
   * @param string $value
   *   The machine name.
   * @param array $element
   *   An array containing the structure of the 'id' element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   Whether or not the template machine name is taken.
   */
  public function templateExists($value, array $element, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $template */
    $template = $form_state->getFormObject()->getEntity();
    return (bool) $this->entityTypeManager->getStorage($template->getEntityTypeId())
      ->getQuery()
      ->condition($template->getEntityType()->getKey('id'), $value)
      ->execute();
  }

}
