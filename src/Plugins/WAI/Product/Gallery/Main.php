<?php

namespace Surikata\Plugins\WAI\Product {
  class Gallery extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["productInfo"] = (new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer))->getProductInfo();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Gallery extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => "Products - Gallery",
      ];
    }
  }
}