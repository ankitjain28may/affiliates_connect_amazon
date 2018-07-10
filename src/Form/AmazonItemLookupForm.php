<?php

namespace Drupal\affiliates_connect_amazon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\affiliates_connect\AffiliatesNetworkManager;
use Drupal\affiliates_connect_amazon\AmazonLocale;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\affiliates_connect\Entity\AffiliatesProduct;

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

  protected $tempStore;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.affiliates_network'),
      $container->get('user.private_tempstore')
    );
  }

  /**
   * AffiliatesConnectController constructor.
   *
   * @param \Drupal\affiliates_connect\AffiliatesNetworkManager $affiliatesNetworkManager
   *   The affiliates network manager.
   */
  public function __construct(AffiliatesNetworkManager $affiliatesNetworkManager, PrivateTempStoreFactory $temp_store_factory) {
    $this->affiliatesNetworkManager = $affiliatesNetworkManager;
    $this->tempStore = $temp_store_factory->get('amazon_search');
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
      '#default_value' => $this->tempStore->get('category'),
    ];

    $form['container']['keyword'] = [
      '#type' => 'textfield',
      '#default_value' => $this->tempStore->get('keyword'),
      '#size' => 60,
      '#maxlength' => 60,
      '#placeholder' => 'Enter a keyword',
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Search'),
    ];

    if ($this->tempStore->get('data')) {
      $form['table'] = [
        '#type' => 'tableselect',
        '#header' => $this->getHeader(),
        '#options' => $this->buildRows(),
        '#multiple' => true,
        '#empty' => $this->t('No products found'),
      ];

      $form['pager'] = [
        '#type' => 'pager'
      ];

      $form['import'] = [
        '#type' => 'submit',
        '#name' => 'import',
        '#button_type' => 'primary',
        '#value' => $this->t('Import'),
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

    $selected_names = array_filter($values['table']);

    $keyword = $values['keyword'];
    $category = $values['category'];
    $some_data;

    $button_clicked = $form_state->getTriggeringElement()['#name'];
    if ($button_clicked == 'import') {
      $this->importProducts($selected_names);
      return;
    }
    if (!$keyword) {
      drupal_set_message($keyword, 'error', FALSE);
    } else {
      if ($category == 'asin') {
        $some_data = $this->amazon->itemLookup($keyword)->execute()->getResults();
      } else {
        $some_data = $this->amazon->itemSearch($keyword, $category)->execute()->getResults();
      }
      $this->tempStore->set('data', $some_data);
    }
      // drupal_set_message(json_encode($some_data), 'error', FALSE);

    $this->tempStore->set('keyword', $keyword);
    $this->tempStore->set('category', $category);

    pager_default_initialize($some_data->TotalPages, 10);
  }

  public function getHeader()
  {
    $header = [
     'image' => $this->t('Image'),
     'name' => $this->t('Product Name'),
     'mrp' => $this->t('M.R.P'),
     'sellingprice' => $this->t('Selling Price'),
   ];
   return $header;
  }

  public function buildRows()
  {
    $row = [];
    $data = $this->tempStore->get('data');
    foreach ($data->Items as $key => $item) {
      $row[$key] = [
        'image' => [
          'data' => [
            '#prefix' => '<div><img src="' . $item->getImage('SmallImage')->URL . '" width=30 height=40> &nbsp;&nbsp;',
            '#suffix' => '</div>',
          ],
        ],
        'name' => [
          'data' => [
            '#prefix' => '<a href="' . $item->URL . '">' . $item->Title . '</a>'
          ],
        ],
        'mrp' => $item->getCurrency() . $item->getPrice(),
        'sellingprice' => $item->getCurrency() . $item->getSellingPrice(),
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

  public function importProducts($element)
  {
    drupal_set_message(json_encode($element), 'error', FALSE);
    $config = \Drupal::configFactory()->get('affiliates_connect_amazon.settings');
    $data = $this->tempStore->get('data');
    foreach ($element as $value) {
      $product = $this->buildImportData($data->Items[$value]);
      drupal_set_message(json_encode($product), 'error', FALSE);
      AffiliatesProduct::createOrUpdate($product, $config);
    }
    drupal_set_message($this->t('Products are imported successfully'), 'status', FALSE);
  }

  public function buildImportData($product_data) {
    $product = [
      'name' => $product_data->Title,
      'plugin_id' => 'affiliates_connect_amazon',
      'product_id' => $product_data->ASIN,
      'product_description' => '',
      'image_urls' => $product_data->getImage('SmallImage')->URL,
      'product_family' => $product_data->ProductGroup,
      'currency' => $product_data->getCurrency(),
      'maximum_retail_price' => $product_data->getPrice(),
      'vendor_selling_price' => $product_data->getSellingPrice(),
      'vendor_special_price' => $product_data->getSellingPrice(),
      'product_url' => $product_data->URL,
      'product_brand' => $product_data->Brand,
      'in_stock' => TRUE,
      'cod_available' => TRUE,
      'discount_percentage' => '',
      'offers' => '',
      'size' => $product_data->Size,
      'color' => $product_data->Color,
      'seller_name' => $product_data->Manufacturer,
      'seller_average_rating' => '',
      'additional_data' => '',
    ];

    return $product;
  }

}
