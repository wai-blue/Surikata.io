<?php

namespace ADIOS\Actions\Orders;

class ChangeOrderState extends \ADIOS\Core\Action {
  public function render() {
    $values = $this->params;
    $id_order = $values["id_order"];
    $new_state = $values["state"];
    // for test purposes
    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->changeOrderState($id_order, ["state" => $new_state]);
    ;
  }
}