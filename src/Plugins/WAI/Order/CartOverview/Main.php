<?php

namespace Surikata\Plugins\WAI\Order {
  class CartOverview extends \Surikata\Core\Web\Plugin {


    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["voucher"] = (
        new \Surikata\Plugins\WAI\Proprietary\Checkout\Vouchers($this->websiteRenderer)
      )->checkVoucher();

      $twigParams["cartContents"] = (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))->getCartContents();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Order {
  class CartOverview extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "enableVouchers" => [
          "title" => $this->translate("Enable vouchers"),
          "type" => "bool",
        ],
      ];
    }
  }
}