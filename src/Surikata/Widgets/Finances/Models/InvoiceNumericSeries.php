<?php

namespace ADIOS\Widgets\Finances\Models;

class InvoiceNumericSeries extends \ADIOS\Core\Widget\Model {
  var $sqlName = "invoice_numeric_series";
  var $lookupSqlValue = "{%TABLE%}.name";
  var $urlBase = "Invoices/NumericSeries";

  public function init() {
    $this->tableTitle = $this->translate("Invoice numeric series");
    $this->formTitleForInserting = $this->translate("New invoice numeric series");
    $this->formTitleForEditing = $this->translate("Invoice numeric series");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Series name"),
        "show_column" => TRUE,
      ],

      "pattern" => [
        "type" => "varchar",
        "title" => $this->translate("Invoice numbering pattern"),
        "show_column" => TRUE,
      ],

      "id_merchant" => [
        "type" => "lookup",
        "model" => "Widgets/Finances/Models/Merchant",
        "title" => $this->translate("Applies to merchant profile"),
        "show_column" => true
      ],
    ]);
  }

}