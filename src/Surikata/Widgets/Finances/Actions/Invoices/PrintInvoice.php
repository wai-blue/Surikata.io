<?php

namespace ADIOS\Actions\Finances\Invoices;

class PrintInvoice extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {
    return [
      "invoice" => (new \ADIOS\Widgets\Finances\Models\Invoice($this->adios))->getById($this->params['id']),
    ];
  }
}