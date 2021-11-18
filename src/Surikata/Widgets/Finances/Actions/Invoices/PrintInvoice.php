<?php

namespace ADIOS\Actions\Finances\Invoices;

use ADIOS\Widgets\Finances\Models\Invoice;
use ADIOS\Widgets\Settings\Models\Unit;

class PrintInvoice extends \ADIOS\Core\Widget\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {

    $language = $this->params["invoiceLanguage"] ?? \ADIOS\Widgets\Finances\Models\Invoice::LANGUAGE_SK;
    
    // REVIEW: staci neskor, default template pocitat podla toho, ci customer je platca DPH
    $template = $this->params["invoiceTemplate"] ?? \ADIOS\Widgets\Finances\Models\Invoice::TEMPLATE_WITH_VAT;
    $invoice = (new Invoice($this->adios))->getById($this->params['id']);

    foreach ($invoice["ITEMS"] as $key => $item) {
      if (is_numeric($item["id_delivery_unit"])) {
        $invoice["ITEMS"][$key]["DELIVERY_UNIT"] = (new Unit())->getById($item["id_delivery_unit"]);
      }
    }

    $invoice["payment_method_value"] = (new Invoice($this->adios))
      ->enumInvoicePaymentMethods[$invoice["payment_method"]]
    ;

    if ($language === "en") {
      switch($invoice["payment_method"]) {
        case Invoice::PAYMENT_METHOD_WIRE_TRANSFER:
          $invoice["payment_method_value"] = "Bank transfer";
          break;
        case Invoice::PAYMENT_METHOD_CASH:
          $invoice["payment_method_value"] = "Cash";
          break;
        case Invoice::PAYMENT_METHOD_CHEQUE:
          $invoice["payment_method_value"] = "Cheque";
          break;
        case Invoice::PAYMENT_METHOD_CARD:
          $invoice["payment_method_value"] = "Card";
          break;
      }
    }

    return [
      "language" => $language,
      "template" => $template,
      "invoice" => $invoice,
    ];
  }
}