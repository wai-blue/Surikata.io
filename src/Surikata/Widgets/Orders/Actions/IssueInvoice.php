<?php

namespace ADIOS\Actions\Orders;

class IssueInvoice extends \ADIOS\Core\Action {
  public function render() {
    echo (new \ADIOS\Widgets\Orders\Models\Order($this->adios))
      ->issueInvoce($this->params['id_order'])
    ;
  }
}