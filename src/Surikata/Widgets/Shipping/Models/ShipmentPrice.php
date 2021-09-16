<?php

namespace ADIOS\Widgets\Shipping\Models;

class ShipmentPrice extends \ADIOS\Core\Model {
  var $sqlName = "shipping_shipment_prices";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/ShipmentPrices";
  var $tableTitle = "Shipment prices";
  var $formTitleForInserting = "New shipment price";
  var $formTitleForEditing = "Shipment price";

  const PRICE_CALCULATION_METHOD_PRICE  = 1;
  const PRICE_CALCULATION_METHOD_WEIGHT  = 2;

  var $shipmentPriceCalculationMethodEnumValues = [
    self::PRICE_CALCULATION_METHOD_PRICE  => "Calculate by offer value",
    self::PRICE_CALCULATION_METHOD_WEIGHT => "Calculate by offer weight"
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

      "shipment_price_calculation_method" => [
        'type' => 'varchar',
        'title' => $this->translate("Shipment price calculation method"),
        "enum_values" => $this->shipmentPriceCalculationMethodEnumValues,
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

      "shipment_price" => [
        "type" => "float",
        "title" => $this->translate("Shipment price"),
        "show_column" => TRUE,
        "required" => TRUE
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

  public function getById(int $idShipment) {
    return reset(
      $this
      ->where('id_shipment', $idShipment)
      ->get()
      ->toArray()
    );
  }

  public function formParams($data, $params) {
    $params['columns']['id_shipment']['onchange'] = "
      {$params['uid']}_generate_unique_name();
    ";
    $params['columns']['price_from']['onchange'] = "
      {$params['uid']}_generate_unique_name();
    ";
    $params['columns']['price_to']['onchange'] = "
      {$params['uid']}_generate_unique_name();
    ";
    $params['columns']['weight_from']['onchange'] = "
      {$params['uid']}_generate_unique_name();
    ";
    $params['columns']['weight_to']['onchange'] = "
      {$params['uid']}_generate_unique_name();
    ";

    $params['columns']['shipment_price_calculation_method']['onchange'] = "
      {$params['uid']}_change_inputs($(this).val());
      {$params['uid']}_generate_unique_name();
    ";
    
    $params["javascript"] = "
      function {$params['uid']}_generate_unique_name() {

        var data = {
          shipmentId: $('#{$params['uid']}_id_shipment').val(),
          method: $('#{$params['uid']}_shipment_price_calculation_method').val(),
          price_from: $('#{$params['uid']}_price_from').val(),
          price_to: $('#{$params['uid']}_price_to').val(),
          weight_from: $('#{$params['uid']}_weight_from').val(),
          weight_to: $('#{$params['uid']}_weight_to').val()
        };

        _ajax_read(
          'Shipping/GetUniqueName', 
          data,
          function(res) {
            $('#{$params['uid']}_name').fadeTo('slow', 0.5).fadeTo('slow', 1.0);
            $('#{$params['uid']}_name').val(res);
          }
        );
      }

      var input = $('#{$params['uid']}_shipment_price_calculation_method');
      var value = input.val();
      var thisRow = $(input).closest('.subrow');
      var offerWeightFrom = thisRow.next('.subrow');
      var offerWeightTo = offerWeightFrom.next('.subrow');
      var offerPriceTo  = offerWeightTo .next('.subrow');
      var offerPriceFrom = offerPriceTo.next('.subrow');

      $(document).ready(function() {
        {$params['uid']}_change_inputs(value);
      });

      function {$params['uid']}_change_inputs(shipmentCalculationMethod) {
        offerWeightFrom.hide();
        offerWeightTo.hide();
        offerPriceTo.hide();
        offerPriceFrom.hide();

        if (shipmentCalculationMethod == ".self::PRICE_CALCULATION_METHOD_PRICE.") {
          offerPriceTo.show();
          offerPriceFrom.show();
        } else if (shipmentCalculationMethod == ".self::PRICE_CALCULATION_METHOD_WEIGHT.") {
          offerWeightFrom.show();
          offerWeightTo.show();
        }
      }
    ";

    return $params;
  }

}