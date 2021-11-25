<?php

namespace ADIOS\Actions\Orders;

use ADIOS\Widgets\Orders\Models\Order;

class PrintOrder extends \ADIOS\Core\Widget\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {

    $order = (new Order($this->adios))->getById($this->params['id']);

    return [
      "order" => $order,
    ];
  }
}