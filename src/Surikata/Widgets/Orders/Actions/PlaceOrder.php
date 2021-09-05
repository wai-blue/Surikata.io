<?php

namespace ADIOS\Actions\Orders;

use ADIOS\Widgets\Orders\Models\Order;

class PlaceOrder extends \ADIOS\Core\Action {
  public function render() {

    $values = json_decode($this->params["values"]);
    $orderModel = new Order($this->adios);
    $orderData = $orderModel->addCustomerInfoToOrderData($this->params);

    return $orderModel
      ->placeOrder($orderData, NULL, NULL, FALSE);

  }
}