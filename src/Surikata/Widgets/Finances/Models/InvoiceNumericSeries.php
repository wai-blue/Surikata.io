<?php

namespace ADIOS\Widgets\Finances\Models;

class InvoiceNumericSeries extends \ADIOS\Core\Model {
  var $sqlName = "invoice_numeric_series";
  var $lookupSqlValue = "{%TABLE%}.name";
  var $urlBase = "Invoices/NumericSeries";
  var $tableTitle = "Invoice numeric series";
  var $formTitleForInserting = "New invoice numeric series";
  var $formTitleForEditing = "Invoice numeric series";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => "varchar",
        "title" => "Series name",
        "show_column" => TRUE,
      ],

      "pattern" => [
        "type" => "varchar",
        "title" => "Series pattern",
        "show_column" => TRUE,
      ],

      "id_merchant" => [
        "type" => "lookup",
        "model" => "Widgets/Finances/Models/Merchant",
        "title" => "Merchant",
        "show_column" => true
      ],
    ]);
  }

}