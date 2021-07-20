<?php

namespace Surikata\Plugins\WAI\Delivery {

  class DPD extends \Surikata\Core\Web\Plugin\Delivery {
    public $heurekaId = "DPD";

    public function getDeliveryMeta() {
      return [
        "name" => "Preprava DPD",
        "description" => "Su najrychlejsi",
      ];
    }

    public function calculatePriceForProduct($product) {
      return 4;
    }

    public function calculatePriceForOrder($orderData, $cartContents) {
      return "109";
    }

  }

}

namespace ADIOS\Plugins\WAI\Delivery {

  class DPD extends \Surikata\Core\AdminPanel\Plugin {

    // tato trieda musi byt vytvorena, inac ju ADIOS nenajde
    // Surikata hlada delivery pluginy zo zoznamu pluginov v ADIOSe

  }

}

