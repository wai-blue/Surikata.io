<?php

namespace ADIOS\Widgets\Shipping\Models;

class Shipment extends \ADIOS\Core\Model {
  var $sqlName = "shipping_shipments";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/Shipments";
  var $tableTitle = "Shipments";
  var $formTitleForInserting = "New shipment";
  var $formTitleForEditing = "Shipment";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Shipment name"),
        'show_column' => TRUE,
        'required' => TRUE
      ],

      "description" => [
        'type' => 'text',
        'title' => $this->translate("Shipment description"),
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => $this->translate("Shipment logo"),
        'show_column' => TRUE,
      ],

      "id_country" => [
        "type" => "lookup",
        "title" => $this->translate("Country"),
        "model" => "Widgets/Shipping/Models/Country",
        "show_column" => TRUE,
        'required' => TRUE
      ],

      "id_delivery_service" => [
        "type" => "lookup",
        "title" => $this->translate("Delivery service"),
        "model" => "Widgets/Shipping/Models/DeliveryService",
        "show_column" => TRUE,
        'required' => TRUE
      ],

      "id_payment_service" => [
        "type" => "lookup",
        "title" => $this->translate("Payment service"),
        "model" => "Widgets/Shipping/Models/PaymentService",
        "show_column" => TRUE,
        'required' => TRUE
      ],

      "is_enabled" => [
        "type" => "boolean",
        "title" => $this->translate("Enable"),
        "show_column" => TRUE
      ],

      "order_index" => [
        "type" => "int",
        "title" => $this->translate("Order index"),
      ]

    ]);
  }

}