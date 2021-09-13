<?php

namespace ADIOS\Widgets\Shipping\Models;

class Country extends \ADIOS\Core\Model {
  var $sqlName = "shipping_countries";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/Countries";
  var $tableTitle = "Shipping countries";
  var $formTitleForInserting = "New shipping country";
  var $formTitleForEditing = "Shipping country";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Country"),
        'show_column' => TRUE,
        'required' => TRUE
      ],

      "flag" => [
        'type' => 'image',
        'title' => $this->translate("Country flag"),
        'show_column' => TRUE,
      ],

      "is_enabled" => [
        'type' => 'boolean',
        'title' => $this->translate("Enabled"),
        'show_column' => TRUE,
      ],

      "order_index" => [
        "type" => "int",
        "title" => $this->translate("Order index"),
      ],
    ]);
  }

}