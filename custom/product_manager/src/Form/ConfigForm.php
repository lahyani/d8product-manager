<?php

/**
 * @file
 * Contains \Drupal\product_manager\Form\ConfigForm.
 */

namespace Drupal\product_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_manager_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Defines settings for the module
    $config = $this->config('product_manager.settings');
    // Defines mail field
    $form['recipient_email'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Recipient Email'),
      '#description'   => $this->t('Email where to send a notification when a new product is created. Leave empty to use site admin email'),
      '#default_value' => $config->get('recipient_email'),
      '#required'      => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('product_manager.settings');
    // Persists submitted data
    $config->set('recipient_email', $form_state->getValue('recipient_email'));
    $config->save();

    return parent::submitForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'product_manager.settings',
    ];
  }
}
