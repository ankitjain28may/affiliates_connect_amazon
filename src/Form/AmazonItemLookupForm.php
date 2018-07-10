<?php

namespace Drupal\affiliates_connect_amazon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\affiliates_connect\AffiliatesNetworkManager;
use Drupal\affiliates_connect_amazon\AmazonLocale;

/**
 * Class AmazonItemLookupForm.
 */
class AmazonItemLookupForm extends FormBase {

  /**
   * The affiliates network manager.
   *
   * @var \Drupal\affiliates_connect\AffiliatesNetworkManager
   */
  private $affiliatesNetworkManager;

   /**
   * The Amazon Locale.
   *
   * @var \Drupal\affiliates_connect_amazon\AmazonLocale
   */
  private $amazon_locale;

  /**
   * The Amazon Instance.
   *
   * @var \Drupal\affiliates_connect_amazon\Plugin\AffiliatesNetwork\AmazonConnect
   */
  private $amazon;

  /**
   * The search data
   * @var array|null
   */
  protected $data;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.affiliates_network')
    );
  }

  /**
   * AffiliatesConnectController constructor.
   *
   * @param \Drupal\affiliates_connect\AffiliatesNetworkManager $affiliatesNetworkManager
   *   The affiliates network manager.
   */
  public function __construct(AffiliatesNetworkManager $affiliatesNetworkManager) {
    $this->affiliatesNetworkManager = $affiliatesNetworkManager;
    $this->amazon_locale = new AmazonLocale();
    $this->amazon = $this->affiliatesNetworkManager->createInstance('affiliates_connect_amazon');
    $this->amazon->setCredentials(
      $this->config('affiliates_connect_amazon.settings')->get('amazon_secret_key'),
      $this->config('affiliates_connect_amazon.settings')->get('amazon_access_key'),
      $this->config('affiliates_connect_amazon.settings')->get('amazon_associate_id')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliates_connect_amazon_item_lookup';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['container'] = array(
      '#type' => 'container',
      '#attributes' => array(
          'class' => array('container-inline'),
      ),
    );

    $form['container']['category'] = [
      '#type' => 'select',
      '#options' => $this->buildCategories(),
      '#attributes' => ['class' => ['button']],
      '#empty_option' => 'Choose a Category',
      '#default_value' => '',
    ];

    $form['container']['keyword'] = [
      '#type' => 'textfield',
      '#default_value' => '',
      '#size' => 60,
      '#maxlength' => 60,
      '#placeholder' => 'Enter a keyword',
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Search'),
    ];

    if ($this->data) {
      $form['data'] = [
        '#type' => 'tableselect',
        '#header' => $this->getHeader(),
        '#options' => $this->buildRows(),
        '#empty' => $this->t('No users found'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $keyword = $values['keyword'];
    $category = $values['category'];

    if ($category == 'asin') {
      $this->data = $this->amazon->itemLookup($keyword)->getLink();
    } else {
      $this->data = $this->amazon->itemSearch($keyword, $category)->getLink();
    }
  }

  public function getHeader()
  {
    $header = [
     'image' => $this->t('Image'),
     'name' => $this->t('Product Name'),
     'description' => $this->t('Description'),
   ];
   return $header;
  }

  public function buildRows()
  {
    $row = [];
    foreach ($this->data as $key => $item) {

      $row[$key] = [
        'image' => $item['id'],
        'name' => $item['title'],
        'description' => $item['body'],
      ];
    }
    return $row;
  }

  public function buildCategories() {
    $locale = $this->config('affiliates_connect_amazon.settings')->get('locale');
    $categories = $this->amazon_locale->getCategories($locale);
    $categories['asin'] = 'Search by ASIN No.';
    return $categories;
  }

}
