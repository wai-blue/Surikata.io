<?php

namespace ADIOS\Actions\Orders;

class AddOrderItem extends \ADIOS\Core\Action {
  public function render() {

    $product = (new \ADIOS\Widgets\Products\Models\Product($this->adios))
      ->addProductToOrder($this->params['id_product'])
    ;
    $result["result"] = "SUCCESS";
    $result["content"] = $product;

    return json_encode($result);

  }
}