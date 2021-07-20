<?php

namespace ADIOS\Plugins\WAI\Product {
  class Price extends \Surikata\Core\AdminPanel\Plugin {
    public function onProductAfterPriceCalculation($event) {
      $idProduct = (int) $event['idProduct'];

      $event["priceInfo"] = [
        "fullPrice" => 999,
        "salePrice" => 999,
        "discountTotal" => $idProduct / 10,
        "calculationSteps" => [
          // ... napr. zoznam zliav alebo marzi
        ],
      ];

      return $event;
    }
  }
}