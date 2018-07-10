<?php

namespace Drupal\affiliates_connect_amazon\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\affiliates_connect\Form\AffiliatesConnectSettingsForm;
use Drupal\affiliates_connect_amazon\AmazonLocale;

/**
 * Class AffiliatesAmazonSettingsForm.
 */
class AffiliatesAmazonSettingsForm extends AffiliatesConnectSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliates_connect_amazon_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array_merge(
      parent::getEditableConfigNames(),
      ['affiliates_connect_amazon.settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('affiliates_connect_amazon.settings');

    $form['amazon_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Affiliates Connect Amazon Settings'),
      '#open' => TRUE,
      '#description' => $this->t('If you are not an Amazon Associate, Please Sign
      up for Amazon Associate program here: <a href="@affiliate-marketing">@affiliate-marketing</a>',
          ['@affiliate-marketing' => 'https://affiliate-program.amazon.in/']),
    ];

    $form['amazon_settings']['locale'] = [
      '#type' => 'select',
      '#options' => $this->getLocale(),
      '#empty_option' => 'Select Locale',
      '#default_value' => $config->get('locale'),
    ];

    $form['amazon_settings']['native_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Native API'),
      '#description' => $this->t('Enable Affiliate Marketing using tracking ID'),
      '#default_value' => $config->get('native_api'),
    ];

    $form['amazon_settings']['native_api_form'] = [
      '#type' => 'details',
      '#title' => $this->t('API Token'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['amazon_settings']['native_api_form']['amazon_associate_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Affiliate Tracking ID'),
      '#default_value' => $config->get('amazon_associate_id'),
      '#size' => 60,
      '#maxlength' => 60,
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];

    $form['amazon_settings']['native_api_form']['amazon_access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Key'),
      '#default_value' => $config->get('amazon_access_key'),
      '#size' => 60,
      '#maxlength' => 60,
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];

    $form['amazon_settings']['native_api_form']['amazon_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret key'),
      '#default_value' => $config->get('amazon_secret_key'),
      '#size' => 60,
      '#maxlength' => 60,
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];

    $form['amazon_settings']['native_api_form']['data_storage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Data Storage'),
      '#description' => $this->t('Enable to store searched product data in your site database.'),
      '#default_value' => $config->get('data_storage'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('affiliates_connect_amazon.settings')
      ->set('locale', $values['locale'])
      ->set('native_api', $values['native_api'])
      ->set('amazon_associate_id', $values['amazon_associate_id'])
      ->set('amazon_access_key', $values['amazon_access_key'])
      ->set('amazon_secret_key', $values['amazon_secret_key'])
      ->set('data_storage', $values['data_storage'])
      ->save();
    parent::submitForm($form, $form_state);
  }


  public function getLocale()
  {
    $amazon_locale = new AmazonLocale();
    return $amazon_locale->getLocale();
  }

}
