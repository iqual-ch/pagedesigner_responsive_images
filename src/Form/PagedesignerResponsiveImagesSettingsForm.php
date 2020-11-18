<?php

namespace Drupal\pagedesigner_responsive_images\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;

/**
 * Add the pagedesigner effects settings form.
 */
class PagedesignerResponsiveImagesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pagedesigner_responsive_images.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pagedesigner_responsive_images_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    parent::buildForm($form, $form_state);
    $config = $this->config('pagedesigner_responsive_images.settings');

    $form['grid_sizes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Set grid sizes'),
      '#default_value' => Yaml::decode($config->get('grid_sizes')),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    try {
      Yaml::encode($form_state->getValue('grid_sizes'));
    }
    catch (InvalidDataTypeException $e) {
      $form_state->setErrorByName(
        'grid_sizes',
        $this->t('The provided configuration is not a valid yaml text.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('pagedesigner_responsive_images.settings');
    $config->set('grid_sizes', Yaml::encode($form_state->getValue('grid_sizes')));
    $config->save();
  }

}
