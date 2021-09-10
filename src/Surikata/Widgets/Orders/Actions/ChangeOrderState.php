<?php

namespace ADIOS\Actions\Orders;

class ChangeOrderState extends \ADIOS\Core\Action {
  public function render() {

    $idOrder = $this->params["id_order"];
    $newState = $this->params["state"];

    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->changeOrderState($idOrder, ["state" => $newState]);
    ;
  }
}