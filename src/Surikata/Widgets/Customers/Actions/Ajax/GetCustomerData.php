<?php

namespace ADIOS\Actions\Customers\Ajax;

class GetCustomerData extends \ADIOS\Core\Action {
  public function render() {
    return (new \ADIOS\Widgets\Customers\Models\Customer($this->adios))->getById((int) $this->params['id']);
  }
}