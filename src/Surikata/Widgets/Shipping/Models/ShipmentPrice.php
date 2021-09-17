<?php

namespace ADIOS\Widgets\Shipping\Models;

class ShipmentPrice extends \ADIOS\Core\Model {
  const DELIVERY_FEE_BY_ORDER_PRICE = 1;
  const DELIVERY_FEE_BY_ORDER_WEIGHT = 2;

  var $sqlName = "shipping_prices";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  // var $urlBase = "DeliveryAndPayment/Prices";
  var $tableTitle = "Shipment prices";
  var $formTitleForInserting = "New shipment price";
  var $formTitleForEditing = "Shipment price";

  var $shipmentPriceCalculationMethodEnumValues = [
    self::DELIVERY_FEE_BY_ORDER_PRICE => "Total value of the order",
    self::DELIVERY_FEE_BY_ORDER_WEIGHT => "Total weight of the order",
  ];

  public function columns(array $columns = []) {
    return parent::columns([
      "id_shipment" => [
        "type" => "lookup",
        "title" => $this->translate("Shipment"),
        "model" => "Widgets/Shipping/Models/Shipment",
        "required" => TRUE,
      ],

      "delivery_fee_calculation_method" => [
        'type' => 'varchar',
        'title' => $this->translate("Delivery price calculation method"),
        "enum_values" => $this->shipmentPriceCalculationMethodEnumValues,
        'show_column' => TRUE,
      ],

      "price_from" => [
        "type" => "float",
        "title" => $this->translate("Order price: From"),
        "show_column" => TRUE
      ],

      "price_to" => [
        "type" => "float",
        "title" => $this->translate("Order price: To"),
        "show_column" => TRUE
      ],

      "weight_from" => [
        "type" => "float",
        "title" => $this->translate("Order weight: From"),
        "show_column" => TRUE
      ],

      "weight_to" => [
        "type" => "float",
        "title" => $this->translate("Order weight: To"),
        "show_column" => TRUE
      ],

      "delivery_fee" => [
        "type" => "float",
        "title" => $this->translate("Delivery extra fee"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "payment_fee" => [
        "type" => "float",
        "title" => $this->translate("Payment extra fee"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "name" => [
        "type" => 'varchar',
        "title" => $this->translate("Name"),
        "description" => $this->translate("Optional. Some design themes may use this value."),
      ],

    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      [
        "type" => "unique",
        "columns" => ["name"],
      ],
      [
        "type" => "index",
        "columns" => ["price_from"],
      ],
      [
        "type" => "index",
        "columns" => ["price_to"],
      ],
      [
        "type" => "index",
        "columns" => ["weight_from"],
      ],
      [
        "type" => "index",
        "columns" => ["weight_to"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "`{$this->table}`.`id_shipment` = ".(int) $params['id_shipment'];
    $params["show_controls"] = FALSE;
    $params["show_search_button"] = FALSE;
    $params["title"] = " ";
    return $params;
  }

  public function getById(int $idShipment) {
    return reset(
      $this
      ->where('id_shipment', $idShipment)
      ->get()
      ->toArray()
    );
  }

  public function getFeesForOrder($orderData) {
    $idDestinationCountry = (int) ($orderData['id_destination_country'] ?? 0);
    $idDeliveryService = (int) ($orderData['id_delivery_service'] ?? 0);
    $idPaymentService = (int) ($orderData['id_payment_service'] ?? 0);
    $priceTotal = (float) ($orderData['SUMMARY']['price_total'] ?? 0);
    $weightTotal = (float) ($orderData['SUMMARY']['weight_total'] ?? 0);

    $deliveryFee = 0;
    $paymentFee = 0;

    $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($this->adminPanel);
    $shipment = reset($shipmentModel
      ->where('id_destination_country', $idDestinationCountry)
      ->where('id_delivery_service', $idDeliveryService)
      ->where('id_payment_service', $idPaymentService)
      ->get()
      ->toArray()
    );

    if ($shipment['id'] > 0) {
      $shipmentPrice = reset(
        $this
        ->where('id_shipment', $shipment['id'])
        ->where('delivery_fee_calculation_method', self::DELIVERY_FEE_BY_ORDER_PRICE)
        ->where('price_from', '<=', $priceTotal)
        ->where('price_to', '>=', $priceTotal)
        ->get()
        ->toArray()
      );

      if ($shipmentPrice === NULL) {
        $shipmentPrice = reset(
          $this
          ->where('id_shipment', $shipment['id'])
          ->where('delivery_fee_calculation_method', self::DELIVERY_FEE_BY_ORDER_WEIGHT)
          ->where('weight_from', '<=', $weightTotal)
          ->where('weight_to', '>=', $weightTotal)
          ->get()
          ->toArray()
        );
      }

      if ($shipmentPrice !== NULL) {
        $deliveryFee = (float) $shipmentPrice['delivery_fee'];
        $paymentFee = (float) $shipmentPrice['payment_fee'];
      }
    }

    return [
      'deliveryFee' => $deliveryFee,
      'paymentFee' => $paymentFee,
    ];
  }

}