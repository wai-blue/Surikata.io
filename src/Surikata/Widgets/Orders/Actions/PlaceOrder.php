<?php

namespace ADIOS\Actions\Orders;

class PlaceOrder extends \ADIOS\Core\Action {
  public function render() {

    $values = json_decode($this->params["values"]);
    $this->params["from_admin"] = $this->params["values"]["from_admin"] === "1";

    return (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->createNewOrder($values->id_customer, $this->params);
    ;

  }
}