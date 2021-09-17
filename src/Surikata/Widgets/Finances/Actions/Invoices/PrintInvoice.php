<?php

namespace ADIOS\Actions\Finances\Invoices;

class PrintInvoice extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;
  const WITH_TAX       = 1;
  const WITHOUT_TAX    = 2;
  const FOREIGN_INV    = 3;

  public $enumTaxSettings = [
    self::WITH_TAX        => 'With Tax',
    self::WITHOUT_TAX     => 'Without Tax',
    self::FOREIGN_INV     => 'Foreign invoice',
  ];

  public function preRender() {

    $language = $this->params["invoiceLanguage"] ?? "sk";
    $taxSetting =
      isset($this->params["taxSetting"])
      ? $this->params["taxSetting"]
      : 1
    ;

    return [
      "language" => $language,
      "taxSetting" => $this->enumTaxSettings[$taxSetting],
      "tax" => $taxSetting,
      "invoice" => (new \ADIOS\Widgets\Finances\Models\Invoice($this->adios))->getById($this->params['id']),
    ];
  }
}