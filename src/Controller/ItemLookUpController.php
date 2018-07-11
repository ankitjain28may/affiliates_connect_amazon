<?php

namespace Drupal\affiliates_connect_amazon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\affiliates_connect\Entity\AffiliatesProduct;
use Drupal\affiliates_connect\AffiliatesNetworkManager;
use Drupal\Core\Config\ImmutableConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Use Native API of Flipkart to collect data.
 */
class ItemLookUpController extends ControllerBase {

  /**
   * The affiliates network manager.
   *
   * @var \Drupal\affiliates_connect\AffiliatesNetworkManager
   */
  private $affiliatesNetworkManager;

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
  }

  public function itemLookUp()
  {
    $amazon = $this->affiliatesNetworkManager->createInstance('affiliates_connect_amazon');
    $config = $this->config('affiliates_connect_amazon.settings');

    $amazon->setCredentials($config->get('amazon_secret_key'), $config->get('amazon_access_key'), $config->get('amazon_associate_id'), $config->get('locale'));

    // $result = $amazon->itemLookup('B01HQ0KCIQ')->execute()->getResults();
    // $amazon->setOption('ItemPage', '2');
    $result = $amazon->itemSearch('shirts pants', 'All')->getLink();

      // 'Operation' => 'ItemSearch',
      // 'Keywords' => 'shirts pants',
      // 'SearchIndex' => 'Apparel',
      // 'Version' => '2011-08-01',
      // 'MerchantId' => 'Amazon',
      // 'ResponseGroup' => 'ItemAttributes,Offers,Reviews,Images',
    // ]);
    return drupal_set_message(print_r($result), TRUE);
    // foreach ($result as $key => $value) {
    //  drupal_set_message(print_r($value-), TRUE);
    // }
    return;
  }
}

