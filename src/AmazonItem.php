<?php

namespace Drupal\affiliates_connect_amazon;

/**
* AmazonItem contain one Amazon product.
* AmazonItem->Author       : Author of the product (Exemple : J.K. Rowling...)
* AmazonItem->Creator      : Creator of the product (Exemple : Nom Prénom, Nom Prénom....)
* AmazonItem->Brand        : Brand of the product (Exemple : Nathan, Ubisoft...)
* AmazonItem->Manufacturer : Manufacturer (Exemple : Ubisoft, EA Games...)
* AmazonItem->ProductGroup : Product Group (Exemple : DVD, BOOKS...)
* AmazonItem->Title        : Title of the product (Iron Man, The Lord Of The Ring : The fellowship of the ring, ...)
* AmazonItem->URL          : URL of the Amazon page of the product (http://www.amazon.com/...)
* AmazonItem->Binding      : Binding of Books (Exemple : Paperback (Broché en francais))
* AmazonItem->Price        : Price of the product in cents (divide by 100 for get the reel price) (Exemple : 6550 = 65.50$)
* AmazonItem->CurrencyCode : Contain the currency use by the price (EUR, USD, ...)
*
*/
class AmazonItem {
  public $Author = '';
  public $ASIN = '';
  public $Brand = '';
  public $Manufacturer = '';
  public $ProductGroup = '';
  public $Title = '';
  public $URL = '';
  public $Binding = '';
  public $Price = '';
  public $SellingPrice = '';
  public $CurrencyCode = '';
  public $Department = '';
  public $Warranty = '';
  public $Size = '';
  public $Color = '';
  private $Images = [];

  const IMAGE_SWATCH     = 'SwatchImage';
  const IMAGE_SMALL      = 'SmallImage';
  const IMAGE_THUMBNAIL  = 'ThumbnailImage';
  const IMAGE_TINY       = 'TinyImage';
  const IMAGE_MEDIUM     = 'MediumImage';
  const IMAGE_LARGE      = 'LargeImage';

  const CURRENCY_EURO = 'EUR';
  const CURRENCY_USD  = 'USD';
  const CURRENCY_GPB  = 'GPB';
  const CURRENCY_JPY  = 'JPY';
  const CURRENCY_INR  = 'INR';

  /**
  * Create an instance of AmazonAdvItem with a SimpleXMLElement object. (->Items)
  *
  * @param SimpleXMLElement $XML
  * @return AmazonAdvItems
  */
  public static function createWithXml($XML) {
    $ItemAttrubutes = $XML->ItemAttributes;

    $AmazonItem = new AmazonItem();

    if(isset($XML->ASIN))
    $AmazonItem->ASIN = (string) $XML->ASIN;

    if(isset($ItemAttrubutes->Binding))
    $AmazonItem->Binding = (string) $ItemAttrubutes->Binding;

    if(isset($ItemAttrubutes->Brand))
    $AmazonItem->Brand = (string) $ItemAttrubutes->Brand;

    if(isset($ItemAttrubutes->Department))
    $AmazonItem->Department = (string) $ItemAttrubutes->Department;

    if(isset($ItemAttrubutes->Manufacturer))
    $AmazonItem->Manufacturer = (string) $ItemAttrubutes->Manufacturer;

    if(isset($ItemAttrubutes->ProductGroup))
    $AmazonItem->ProductGroup = (string) $ItemAttrubutes->ProductGroup;

    if(isset($ItemAttrubutes->Title))
    $AmazonItem->Title = (string) $ItemAttrubutes->Title;

    if(isset($ItemAttrubutes->Warranty))
    $AmazonItem->Warranty = (string) $ItemAttrubutes->Warranty;

    if(isset($ItemAttrubutes->ListPrice->Amount))
    $AmazonItem->Price = (int) $ItemAttrubutes->ListPrice->Amount;

    if(isset($ItemAttrubutes->ListPrice->CurrencyCode))
    $AmazonItem->CurrencyCode = (string) $ItemAttrubutes->ListPrice->CurrencyCode;


    if(isset($XML->DetailPageURL))
    $AmazonItem->URL = (string) $XML->DetailPageURL;

    if (isset($XML->OfferSummary->LowestNewPrice))
    $AmazonItem->SellingPrice = (int) $XML->OfferSummary->LowestNewPrice->Amount;

    if(isset($ItemAttrubutes->Author))
    $AmazonItem->Author = (string) $ItemAttrubutes->Author;

    if(isset($ItemAttrubutes->Size))
    $AmazonItem->Size = (string) $ItemAttrubutes->Size;

    if(isset($ItemAttrubutes->Color))
    $AmazonItem->Color = (string) $ItemAttrubutes->Color;


    $AmazonImageSet = $XML->ImageSets->ImageSet;
    if(isset($XML->ImageSets->ImageSet->SwatchImage))
    $AmazonItem->Images[AmazonItem::IMAGE_SWATCH] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->SwatchImage);
    if(isset($XML->ImageSets->ImageSet->SmallImage))
    $AmazonItem->Images[AmazonItem::IMAGE_SMALL] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->SmallImage);
    if(isset($XML->ImageSets->ImageSet->ThumbnailImage))
    $AmazonItem->Images[AmazonItem::IMAGE_THUMBNAIL] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->ThumbnailImage);
    if(isset($XML->ImageSets->ImageSet->TinyImage))
    $AmazonItem->Images[AmazonItem::IMAGE_TINY] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->TinyImage);
    if(isset($XML->ImageSets->ImageSet->MediumImage))
    $AmazonItem->Images[AmazonItem::IMAGE_MEDIUM] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->MediumImage);
    if(isset($XML->ImageSets->ImageSet->LargeImage))
    $AmazonItem->Images[AmazonItem::IMAGE_LARGE] = AmazonImage::createWithXml($XML->ImageSets->ImageSet->LargeImage);

    return $AmazonItem;
  }

  /**
  * Return currency symbol of $this->CurrencyCode
  *
  * @return string
  */
  public function getCurrency() {
      switch($this->CurrencyCode) {
          case AmazonItem::CURRENCY_EURO :
              return '&euro;';
          break;
          case AmazonItem::CURRENCY_USD :
              return '$';
          break;
          case AmazonItem::CURRENCY_JPY :
              return '&yen';
          break;
          case AmazonItem::CURRENCY_GPB :
              return '&pound';
          break;
          case AmazonItem::CURRENCY_INR :
              return '₹';
          break;
      }
      return '';
  }

  /**
  * Return $this->Price divide by 100. (Exemple : 16.2, 99.99)
  *
  * @return string
  */
  public function getPrice() {
      if($this->Price == '')
      return '';

      return round($this->Price/100, 2);
  }

  public function getSellingPrice() {
      if($this->SellingPrice == '')
      return '';

      return round($this->SellingPrice/100, 2);
  }
  /**
  * Return $this->getPrice(), with $this->getCurrency() (Exemple : 16.5€, 9.33$)
  *
  * @return string
  */
  public function getPriceWithCurrency() {
      return $this->getPrice().$this->getCurrency();
  }

  /**
  * Return an AmazonImage object.
  *
  * @param string $size Use constant IMAGE_(.*) of AmazonAdvItem class
  * @return AmazonImage
  */
  public function getImage($size) {
      return $this->Images[$size];
  }

  public function __toString() {
      return 'AmazonItem';
  }
}
?>
