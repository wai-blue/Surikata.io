<?php

namespace Surikata\Plugins\WAI\Product {
  class Price extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Price extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => "Products - Price",
      ];
    }

    public function onProductGetPriceInfoForSingleProduct($event) {
      $idProduct = (int) $event['idProduct'];
      $event["priceInfo"]["salePriceExclVAT"] = $idProduct;
      $event["priceInfo"]["fullPriceExclVAT"] = $idProduct*1.22;
      $event["priceInfo"]["salePriceInclVAT"] = $idProduct * 1.2;
      $event["priceInfo"]["fullPriceInclVAT"] = $idProduct*1.22 * 1.2;
      return $event;
    }
  }
}