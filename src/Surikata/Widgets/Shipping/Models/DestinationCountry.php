<?php

namespace ADIOS\Widgets\Shipping\Models;

class DestinationCountry extends \ADIOS\Core\Widget\Model {
  var $sqlName = "shipping_destination_countries";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "DeliveryAndPayment/Countries";

  public function init() {
    $this->tableTitle = $this->translate("Countries of destination");
    $this->formTitleForInserting = $this->translate("New country of destination");
    $this->formTitleForEditing = $this->translate("Country of destination");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => 'varchar',
        "title" => $this->translate("Country"),
        "show_column" => TRUE,
        "required" => TRUE
      ],

      "short" => [
        "type" => 'varchar',
        "title" => $this->translate("Short"),
        "description" => $this->translate("E.g.: EN, US, SK, FR, ..."),
        "show_column" => TRUE,
        "required" => TRUE
      ],

      "flag" => [
        "type" => 'image',
        "title" => $this->translate("Flag"),
        "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg."),
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

  public function indexes(array $indexes = []) {
    return parent::indexes([
      [
        "type" => "index",
        "columns" => ["short"],
      ],
    ]);
  }

}