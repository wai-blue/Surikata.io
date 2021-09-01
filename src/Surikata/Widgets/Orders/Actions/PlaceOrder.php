<?php

namespace ADIOS\Actions\Orders;

class PlaceOrder extends \ADIOS\Core\Action {
  public function render() {

    $values = json_decode($this->params["values"]);
    // REVIEW: Co znamena "for test purposes"? Je to este nedokoncene?
    $this->params["from_admin"] = true; // for test purposes
    // REVIEW: render NESMIE robit echo! Iba return.
    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->createNewOrder($values->id_customer, $this->params);
    ;

  }
}