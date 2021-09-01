<?php

namespace ADIOS\Actions\Orders;

class ChangeOrderState extends \ADIOS\Core\Action {
  public function render() {
    $values = $this->params;
    // REVIEW: $camelCase prosim
    $id_order = $values["id_order"];
    // REVIEW: $camelCase prosim
    $new_state = $values["state"];

    // for test purposes
    // REVIEW: render NESMIE robit echo! Iba return.
    // REVIEW: Co znamena "for test purposes"? Je to este nedokoncene?
    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->changeOrderState($id_order, ["state" => $new_state]);
    ;
  }
}