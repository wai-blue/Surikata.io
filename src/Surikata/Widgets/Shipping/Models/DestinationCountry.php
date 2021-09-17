<?php

namespace ADIOS\Widgets\Shipping\Models;

class DestinationCountry extends \ADIOS\Core\Model {
  var $sqlName = "shipping_destination_countries";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/Countries";
  var $tableTitle = "Countries of destination";
  var $formTitleForInserting = "New country of destination";
  var $formTitleForEditing = "Country of destination";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => 'varchar',
        "title" => $this->translate("Country"),
        "show_column" => TRUE,
        "required" => TRUE
      ],

      "flag" => [
        "type" => 'image',
        "title" => $this->translate("Flag"),
        "show_column" => TRUE,
      ],

      "is_enabled" => [
        "type" => 'boolean',
        "title" => $this->translate("Enabled"),
        "show_column" => TRUE,
      ],

      "order_index" => [
        "type" => "int",
        "title" => $this->translate("Order index"),
      ],
    ]);
  }

}