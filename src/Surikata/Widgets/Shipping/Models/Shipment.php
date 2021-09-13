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

  public function price() {
    return $this->hasOne(\ADIOS\Widgets\Shipping\Models\ShipmentPrice::class, 'id_shipment', 'id');
  }

  public function payment() {
    return $this->hasOne(\ADIOS\Widgets\Shipping\Models\PaymentService::class, "id", "id_payment_service");
  }

  public function delivery() {
    return $this->hasOne(\ADIOS\Widgets\Shipping\Models\DeliveryService::class, "id", "id_delivery_service");
  }

  public function country() {
    return $this->hasOne(\ADIOS\Widgets\Shipping\Models\Country::class, "id", "id_country");
  }

  public function getByIdDeliveryService($idDelivery) {
    return $this
      ->with('payment')
      ->where([
        ['id_delivery_service', $idDelivery],
        ['is_enabled', 1]
      ])
      ->get()
      ->toArray()
    ;
  }

  public function getByCartSummary(array $summary) {
    $priceTotal = $summary['priceTotal'];

    return 
      $this
      ->with([
        'price', 
        'delivery',
        'payment',
        'country',
      ])
      ->whereHas('price', function ($q) use ($priceTotal){
        $q->where([
          ['shipment_price_calculation_method', '=', 1],	
          ['price_from', '<=', $priceTotal],
          ['price_to', '>=', $priceTotal]
        ]);
        $q->orWhere([
          ['shipment_price_calculation_method', '=', 2],	
          ['weight_from', '<=', $priceTotal],
          ['weight_to', '>=', $priceTotal]
        ]);
      })
      ->get()
      ->toArray()
    ;
  }

  public function getShipment($idDelivery, $idPayment) {
    $query =
      $this
      ->where([
        ['id_delivery_service', '=', $idDelivery],
        ['id_payment_service', '=', $idPayment]
      ])
      ->get()
      ->toArray()
    ;

    return $query ? reset($query) : NULL;
  }

}