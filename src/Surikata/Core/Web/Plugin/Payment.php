<?php

namespace Surikata\Core\Web\Plugin;

class Payment extends \Surikata\Core\Web\Plugin {
  public static $isPaymentPlugin = TRUE;

  public function getPaymentMeta() {
    return [
      "name" => "PaymentMethodName",
      "description" => "PaymentMethodDescription",
    ];
  }
}