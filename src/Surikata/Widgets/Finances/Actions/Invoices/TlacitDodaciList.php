<?php

namespace ADIOS\Actions\Finances\Invoices;

class TlacitDodaciList extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {
    return [
      "invoice" => $this->adios->getModel('Widgets/Finances/Models/Invoices')->getById($this->params['id']),
    ];
  }
}