<?php

namespace ADIOS\Actions\Orders;

// REVIEW: Musi existovat aj funkcionalita na oznacenie ako nezaplatena.
// Moze sa omylom stat, ze niekto nastavi objednavku ako zaplatenu a potrebuje to vratit spat.
class SetAsPaid extends \ADIOS\Core\Action {
  public function render() {

    $idOrder = $this->params["id_order"];

    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->setAsPaid($idOrder);
    ;
  }
}