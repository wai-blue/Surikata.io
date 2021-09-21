<?php

namespace ADIOS\Actions\Finances\Invoices;

class PrintInvoice extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {

    $language = $this->params["invoiceLanguage"] ?? \ADIOS\Widgets\Finances\Models\Invoice::LANGUAGE_SK;
    
    // REVIEW: staci neskor, default template pocitat podla toho, ci customer je platca DPH
    $template = $this->params["template"] ?? \ADIOS\Widgets\Finances\Models\Invoice::TEMPLATE_WITH_VAT;

    return [
      "language" => $language,
      "template" => $template,
      "invoice" => (new \ADIOS\Widgets\Finances\Models\Invoice($this->adios))->getById($this->params['id']),
    ];
  }
}