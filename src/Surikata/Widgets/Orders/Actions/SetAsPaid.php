<?php

namespace ADIOS\Actions\Orders;

class SetAsPaid extends \ADIOS\Core\Action {
  public function render() {

    $idOrder = $this->params["id_order"];

    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->setPaidValue($idOrder, true);
    ;
  }
}