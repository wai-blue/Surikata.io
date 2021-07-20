<?php

namespace Surikata\Plugins\WAI\Delivery {

  class UPS extends \Surikata\Core\Web\Plugin\Delivery {
    public $heurekaId = "UPS";

    public function getDeliveryMeta() {
      return [
        "name" => "Preprava UPS",
        "description" => "Su najlacnejsi",
      ];
    }

    public function calculatePriceForProduct($product) {
      return 5;
    }
    
    public function calculatePriceForOrder($orderData, $cartContents) {
      $priceTotal = 0;
      foreach ($cartContents["items"] as $item) {
        $priceTotal += $item["PRODUCT"]["weight"];
      }

      return $priceTotal / 10;
    }

  }

}

namespace ADIOS\Plugins\WAI\Delivery {

  class UPS extends \Surikata\Core\AdminPanel\Plugin {

    // tato trieda musi byt vytvorena, inac ju ADIOS nenajde
    // Surikata hlada delivery pluginy zo zoznamu pluginov v ADIOSe

  }

}

