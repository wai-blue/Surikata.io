<?php

namespace Surikata\Plugins\WAIproprietary\Checkout {
  class Vouchers extends \Surikata\Core\Web\Plugin {

    public function renderJSON() {
      $returnArray = [];
      $voucherName = $this->websiteRenderer->urlVariables['voucherName'] ?? "";
      $voucherModel = new \ADIOS\Widgets\Customers\Models\Voucher($this->adminPanel);

      try {
        if ($voucherName == "") {
          throw new \ADIOS\Widgets\Customers\Exceptions\VoucherIsEmpty();
        }

        $voucher = $voucherModel->getVoucherByName($voucherName);

        if (!empty($voucher)) {
          $returnArray["status"] = "OK";
        } else {
          throw new \ADIOS\Widgets\Customers\Exceptions\VoucherIsNotValid();
        }
      } catch (
        \ADIOS\Widgets\Customers\Exceptions\VoucherIsEmpty
        $e
      ) {
        $returnArray["status"] = "FAIL";
        $returnArray["exception"] = get_class($e);
        $returnArray["error"] = $e->getMessage();
      }

      return $returnArray;

    }
    
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      //_print_r($voucherModel->getAllValidated());

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAIproprietary\Checkout {
  class Vouchers extends \Surikata\Core\AdminPanel\Plugin {

  }
}