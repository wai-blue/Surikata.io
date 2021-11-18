<?php

namespace ADIOS\Widgets\Customers\Models;

class Voucher extends \ADIOS\Core\Widget\Model {
  var $sqlName = "vouchers";
  var $urlBase = "Customers/Vouchers";
  var $tableTitle = "Vouchers";

  public function init() {
    $this->tableTitle = $this->translate("Vouchers");
    $this->formTitleForInserting = $this->translate("New voucher");
    $this->formTitleForEditing = $this->translate("Voucher");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "voucher" => [
        "type" => "varchar",
        "title" => $this->translate("Voucher"),
        "show_column" => TRUE,
      ],

      "discount_sum" => [
        "type" => "float",
        "title" => $this->translate("Discount"),
        "unit" => $this->adios->locale->currencySymbol(),
        "description" => $this->translate("Absolute discount value, e.g. 100,- ").$this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "discount_percentage" => [
        "type" => "float",
        "title" => $this->translate("Discount"),
        "unit" => "%",
        "description" => $this->translate("Relative discount value as a percentage from the total order value, e.g. 5 %"),
        "show_column" => TRUE,
      ],

      "valid" => [
        "type" => "bool",
        "title" => $this->translate("Valid"),
        "description" => $this->translate("Invalid vouchers can't be used."),
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