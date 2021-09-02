<?php

namespace Surikata\Core\Web\Plugin;

class Delivery extends \Surikata\Core\Web\Plugin {
  public static $isDeliveryPlugin = TRUE;

  public function getDeliveryMeta() {
    $deliveryServiceModel = new \ADIOS\Widgets\Shipping\Models\DeliveryService($this->adios);

    return $deliveryServiceModel->getByPluginName($this->name);
  }

  public function calculatePriceForProduct($product) {
    return 0;
  }

  public function calculatePriceForOrder($orderData, $cartContents) {
    return 0;
  }
}