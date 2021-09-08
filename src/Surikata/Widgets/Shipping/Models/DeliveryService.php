<?php

namespace ADIOS\Widgets\Shipping\Models;

class DeliveryService extends \ADIOS\Core\Model {
  var $sqlName = "delivery_services";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/DeliveryServices";
  var $tableTitle = "Delivery services";
  var $formTitleForInserting = "New delivery service";
  var $formTitleForEditing = "Delivery service";

  var $deliveryPluginsEnumValues = [];

  public function init() {
    $this->deliveryPluginsEnumValues = [
      "" => "-- Select a delivery plugin --",
    ];

    foreach ($this->adios->websiteRenderer->getDeliveryPlugins() as $deliveryPlugin) {
      $this->deliveryPluginsEnumValues[$deliveryPlugin->name] = $deliveryPlugin->name;
    }
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Delivery company name"),
        'required' => TRUE,
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'varchar',
        'title' => $this->translate("Delivery company description"),
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => $this->translate("Delivery company logo"),
        'show_column' => TRUE,
      ],

      "is_enabled" => [
        'type' => 'boolean',
        'title' => $this->translate("Enabled"),
        'description' => 'If not enabled, this service will not be available at the checkout.',
        'show_column' => TRUE,
      ],

      "connected_plugin" => [
        'type' => 'varchar',
        'title' => $this->translate("Connected delivery plugin"),
        "enum_values" => $this->deliveryPluginsEnumValues,
        'show_column' => TRUE,
      ],
    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "connected_plugin" => [
        "type" => "index",
        "columns" => ["connected_plugin"],
      ]
    ]);
  }

  public function getByPluginName($pluginName) {
    /*$item = reset($this
      ->where([
        ["connected_plugin", "=", $pluginName],
        ["is_enabled", "=", TRUE]
      ])
      ->get()
      ->toArray()
    );

    return (is_array($item) ? $item : FALSE);
    */

    $item = [
      "name" => "Name: {$pluginName}",
      "description" => "Desc: {$pluginName}",
    ];

    return (is_array($item) ? $item : FALSE);
  }

  public function getEnumValues() {
    $enumDeliveryServices = [
      "" => "Nezvolený",
    ];
    foreach ($this->getAll() as $deliveryService) {
      if ($deliveryService["is_enabled"] == 1) {
        $enumDeliveryServices[$deliveryService["id"]] = $deliveryService["name"];
      }
    }
    return $enumDeliveryServices;
  }

}