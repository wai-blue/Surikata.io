<?php

namespace ADIOS\Widgets\Customers\Models;

class Voucher extends \ADIOS\Core\Model {
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
        "required" => TRUE
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

      "max_use" => [
        "type" => "int",
        "title" => $this->translate("Max use of voucher"),
        "description" => $this->translate("Maximum number of voucher uses, e.g. 15x"),
        "show_column" => TRUE,
      ],

      "valid_from" => [
        "type" => "datetime",
        "title" => $this->translate("Valid from"),
        "description" => $this->translate("The date from which the voucher is enabled for use"),
        "show_column" => TRUE,
        "required" => TRUE
      ],

      "valid_to" => [
        "type" => "datetime",
        "title" => $this->translate("Valid to"),
        "description" => $this->translate("The date until which the voucher can be used"),
        "show_column" => TRUE,
        "required" => TRUE
      ],

      "is_enabled" => [
        "type" => "boolean",
        "title" => $this->translate("Enabled"),
        "description" => $this->translate("The voucher is enabled for use"),
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
      "voucher_is_enabled" => [
        "type" => "index",
        "columns" => ["voucher", "is_enabled"],
      ],
    ]);
  }

  public function getVoucherByName(string $voucherName) {
    $voucher = 
      $this
      ->where("name", $voucherName)
      ->where('is_enabled', 1)
      ->where('valid_from', '<=', date('Y-m-d H:i:s', time()))
      ->where('valid_to', '>=', date('Y-m-d H:i:s', time()))
      ->get()
      ->toArray()
    ;

    return $voucher ?? [];
  }

}