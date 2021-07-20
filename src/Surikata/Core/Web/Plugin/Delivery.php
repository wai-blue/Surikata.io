<?php

namespace Surikata\Core\Web\Plugin;

class Delivery extends \Surikata\Core\Web\Plugin {
  public static $isDeliveryPlugin = TRUE;

  public function getDeliveryMeta() {
    return [
      "name" => "DeliveryServiceName",
      "description" => "DeliveryServiceDescription",
    ];
  }

  public function calculatePriceForProduct($product) {
    return 0;
  }

  public function calculatePriceForOrder($orderData, $cartContents) {
    return 0;
  }
}