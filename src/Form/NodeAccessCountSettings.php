<?php

namespace Drupal\node_access_count\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configuration form for Node Access Count settings.
 */
class NodeAccessCountSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'node_access_count_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['node_access_count.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('node_access_count.settings');
    $content_types = NodeType::loadMultiple();

    $form['description'] = [
      '#markup' => $this->t('Select which content types should be tracked for admin views and API access.'),
    ];

    $form['tracking'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Content type'),
        $this->t('Node View'),
        $this->t('Track API Access'),
      ],
    ];

    foreach ($content_types as $type) {
      $type_id = $type->id();
      $label = $type->label();
      $settings = $config->get("tracking.$type_id") ?? [];

      $form['tracking'][$type_id]['label'] = [
        '#markup' => $label,
      ];
      $form['tracking'][$type_id]['admin'] = [
        '#type' => 'checkbox',
        '#default_value' => $settings['admin'] ?? FALSE,
      ];
      $form['tracking'][$type_id]['api'] = [
        '#type' => 'checkbox',
        '#default_value' => $settings['api'] ?? FALSE,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValue('tracking');
    $config = $this->configFactory()->getEditable('node_access_count.settings');
    $config->set('tracking', $values)->save();
  }

}
