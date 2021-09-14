<?php

namespace ADIOS\Plugins\WAI\Product {
  class Price extends \Surikata\Core\AdminPanel\Plugin {
    public function onProductGetPriceInfoForSingleProduct($event) {
      $idProduct = (int) $event['idProduct'];
      $event["priceInfo"]["salePrice"] = $idProduct;
      $event["priceInfo"]["fullPrice"] = $idProduct*1.22;
      return $event;
    }
  }
}