<?php

namespace ADIOS\Widgets\Shipping\Models;

class ShipmentPrice extends \ADIOS\Core\Model {
  var $sqlName = "shipping_shipment_prices";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/ShipmentPrices";
  var $tableTitle = "Shipment prices";
  var $formTitleForInserting = "New shipment price";
  var $formTitleForEditing = "Shipment price";

  var $shipmentPriceCalculationMethodEnumValues = [
    1 => "Calculate by offer value",
    2 => "Calculate by offer weight"
  ];

  public function columns(array $columns = []) {
    return parent::columns([
      "id_shipment" => [
        "type" => "lookup",
        "title" => $this->translate("Shipment"),
        "model" => "Widgets/Shipping/Models/Shipment",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Unique name"),
        "required" => TRUE,
        'show_column' => TRUE,
      ],

      "weight_from" => [
        "type" => "float",
        "title" => $this->translate("Offer weight from"),
        "show_column" => TRUE
      ],

      "weight_to" => [
        "type" => "float",
        "title" => $this->translate("Offer weight to"),
        "show_column" => TRUE
      ],

      "price_from" => [
        "type" => "float",
        "title" => $this->translate("Offer price start"),
        "show_column" => TRUE
      ],

      "price_to" => [
        "type" => "float",
        "title" => $this->translate("Offer price to"),
        "show_column" => TRUE
      ],

      "shipment_price_calculation_method" => [
        'type' => 'varchar',
        'title' => $this->translate("Shipment price calculation method"),
        "enum_values" => $this->shipmentPriceCalculationMethodEnumValues,
        'show_column' => TRUE,
      ],

      "shipment_price" => [
        "type" => "float",
        "title" => $this->translate("Shipment price"),
        "show_column" => TRUE
      ],

    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "unique_name" => [
        "type" => "unique",
        "columns" => ["name"],
      ],
    ]);
  }

  public function shipment() {
    return $this->hasOne(\ADIOS\Widgets\Shipping\Models\Shipment::class, 'id', 'id_shipment');
  }

  public function getAll(string $keyBy = "id") {
    return self::with('shipment')->get()->toArray();
  }

  /*public function getAllBySummary($summary) {

    return 
      self::with('shipment')
      ->where([
        ['price_from', '<=', $summary['priceTotal']],
        ['price_to', '>=', $summary['priceTotal']]
      ])
      ->get()->toArray()
    ;
  }*/
  
  public function getAllBySummary($summary) {
    $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($this->adminPanel);
    $deliveryServiceModel = new \ADIOS\Widgets\Shipping\Models\DeliveryService($this->adminPanel);

    return $this->adios->db->get_all_rows_query("
      SELECT 
        {$deliveryServiceModel->table}.name as name,
        {$this->table}.shipment_price as shipment_price,
        {$deliveryServiceModel->table}.id as id_delivery_service
      FROM {$this->table}
      LEFT JOIN 
        {$shipmentModel->table}
      ON 
        {$this->table}.id_shipment = {$shipmentModel->table}.id 
      LEFT JOIN 
        {$deliveryServiceModel->table}
      ON
        {$deliveryServiceModel->table}.id = {$shipmentModel->table}.id_delivery_service
      WHERE 
      (
        {$this->table}.shipment_price_calculation_method = 1	
        AND {$this->table}.price_from <= {$summary['priceTotal']}
        AND {$this->table}.price_to >= {$summary['priceTotal']}
      ) OR 
      (
        {$this->table}.shipment_price_calculation_method = 2
        AND {$this->table}.weight_from <= 0
        AND {$this->table}.weight_to >= 100
      )
    ");
  }

}