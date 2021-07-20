<?php

namespace ADIOS\Widgets\Customers\Models;

class Voucher extends \ADIOS\Core\Model {
  var $sqlName = "vouchers";
  var $urlBase = "Customers/Vouchers";
  var $tableTitle = "Vouchers";
  var $formTitleForInserting = "Voucher";
  var $formTitleForEditing = "Voucher";

  public function columns(array $columns = []) {
    return parent::columns([
      "voucher" => [
        "type" => "varchar",
        "title" => "Voucher",
        "show_column" => TRUE,
      ],

      "discount_sum" => [
        "type" => "float",
        "title" => "Discount",
        "unit" => $this->adios->locale->currencySymbol(),
        "description" => "Absolute discount value, e.g. 100,- ".$this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "discount_percentage" => [
        "type" => "float",
        "title" => "Discount",
        "unit" => "%",
        "description" => "Relative discount value as a percentage from the total order value, e.g. 5 %",
        "show_column" => TRUE,
      ],

      "valid" => [
        "type" => "bool",
        "title" => "Valid",
        "description" => "Invalid vouchers can't be used.",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "voucher" => [
        "type" => "index",
        "columns" => ["voucher"],
      ],
      "voucher_valid" => [
        "type" => "index",
        "columns" => ["voucher", "valid"],
      ],
    ]);
  }

}