<?php

namespace ADIOS\Actions\Finances\Invoices;

class TlacitDodaciList extends \ADIOS\Core\Widget\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {
    return [
      "invoice" => $this->adios->getModel('Widgets/Finances/Models/Invoices')->getById($this->params['id']),
    ];
  }
}