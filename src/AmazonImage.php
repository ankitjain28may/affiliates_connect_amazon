<?php

namespace Drupal\affiliates_connect_amazon;

/**
* AmazonAdvItems contain image informations of product.
* AmazonAdvItems->URL    : URL of the picture
* AmazonAdvItems->Width  : Width of the picture
* AmazonAdvItems->Height : Height of the picture
*
*/
class AmazonImage {

    public $URL = '';
    public $Width = '';
    public $Height = '';
    /**
    * Create an instance of AmazonItem with a SimpleXMLElement object. (->Items->ImageSets->ImageSet->(.*))
    *
    * @param SimpleXMLElement $XML
    * @return AmazonAdvImage
    */
    public static function createWithXml($XML) {
        $image = new AmazonImage();
        $image->URL = (string) $XML->URL;
        $image->Width = (int) $XML->Height;
        $image->Height = (int) $XML->Width;

        return $image;
    }

    public function __toString() {
        return 'AmazonImage';
    }
}
