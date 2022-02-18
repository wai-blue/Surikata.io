<?php

namespace ADIOS\Actions\Orders;

use ADIOS\Widgets\Orders\Models\Order;

class PrintOrder extends \ADIOS\Core\Widget\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {

    $order = (new Order($this->adios))->getById($this->params['id']);
    $order = (new Order($this->adios))->getExtendedData($order);

    foreach ($order["ITEMS"] as $key => $item) {
      if (is_numeric($item["id_delivery_unit"])) {
        $invoice["ITEMS"][$key]["DELIVERY_UNIT"] = (new \ADIOS\Widgets\Settings\Models\Unit($this->adios))->getById((int)$item["id_delivery_unit"]);
      }
    }
    _print_r($order);
    return [
      "order" => $order,
      "template" => 1
    ];
  }
}