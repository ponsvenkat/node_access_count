<?php

namespace Drupal\node_access_count\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for API endpoint tracking.
 */
class NodeAccessApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['node_access_count.api_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'node_access_count_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('node_access_count.api_settings');

    $form['api_endpoints'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API endpoints to track'),
      '#description' => $this->t('Enter one API endpoint path per line (e.g., /api/node/*). Wildcards (*) are supported.'),
      '#default_value' => $config->get('api_endpoints') ? implode("\n", $config->get('api_endpoints')) : '',
      '#rows' => 10,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = array_filter(array_map('trim', explode("\n", $form_state->getValue('api_endpoints'))));

    $this->configFactory()->getEditable('node_access_count.api_settings')
      ->set('api_endpoints', $values)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
