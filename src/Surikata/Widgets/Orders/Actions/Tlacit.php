<?php

namespace ADIOS\Actions\Orders;

use \ADIOS\Core\Models\Orders as DB;

class Tlacit extends \ADIOS\Core\Action {
  public function preRender() {
    $adios = $this->adios;
    $gtp = $this->adios->gtp;

    $orderModel = new DB\Order($adios);

    return [
      "order" => $orderModel->getById($this->params['id_order']),
    ];
  }
}