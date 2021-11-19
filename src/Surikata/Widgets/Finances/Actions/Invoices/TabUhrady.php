<?php

namespace ADIOS\Actions\Finances\Invoices;

class TabUhrady extends \ADIOS\Core\Widget\Action {
  public function preRender() {
    $invoice = $this->adios
      ->getModel('Widgets/Finances/Models/Invoice')
      ->getById($this->params['id_invoice'])
    ;

    return [
      "invoice" => $invoice,
    ];
  }
}