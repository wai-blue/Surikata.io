<?php

namespace ADIOS\Actions\Orders;

use ADIOS\Widgets\Orders\Models\Order;

class PlaceOrder extends \ADIOS\Core\Widget\Action {
  public function render() {

    $values = json_decode($this->params["values"] ?? "", true);
    if (!is_array($values)) {
      throw new \ADIOS\Widgets\Orders\Exceptions\InvalidOrderDataFormat();
    }

    $orderModel = new Order($this->adios);
    $orderData = $orderModel->addCustomerInfoToOrderData($values);

    return $orderModel
      ->placeOrder($orderData, NULL, NULL, FALSE)
    ;

  }
}