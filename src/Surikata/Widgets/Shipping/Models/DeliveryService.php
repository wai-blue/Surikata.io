<?php

namespace ADIOS\Widgets\Shipping\Models;

class DeliveryService extends \ADIOS\Core\Model {
  var $sqlName = "shipping_delivery_services";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "DeliveryAndPayment/DeliveryServices";

  var $deliveryPluginsEnumValues = [];

  public function init() {
    $this->tableTitle = $this->translate("Delivery services");
    $this->formTitleForInserting = $this->translate("New delivery service");
    $this->formTitleForEditing = $this->translate("Delivery service");

    $this->deliveryPluginsEnumValues = [
      "" => $this->translate("-- Select a plugin --"),
    ];

    foreach ($this->adios->websiteRenderer->getDeliveryPlugins() as $deliveryPlugin) {
      $this->deliveryPluginsEnumValues[$deliveryPlugin->name] = $deliveryPlugin->name;
    }

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => 'varchar',
        "title" => $this->translate("Name"),
        "description" => $this->translate("Name of the service as it will be displayed on the web."),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "description" => [
        "type" => 'varchar',
        "title" => $this->translate("Description"),
        "description" => $this->translate("Optional. Some design themes may display this description on the web."),
        "show_column" => TRUE,
      ],

      "logo" => [
        "type" => 'image',
        "title" => $this->translate("Logo"),
        "description" => 
          $this->translate("Optional. Some design themes may display the logo on the web.")
          ."\n".$this->translate("Supported image extensions: jpg, gif, png, jpeg.")
        ,
        "show_column" => TRUE,
      ],

      "is_enabled" => [
        "type" => 'boolean',
        "title" => $this->translate("Enabled"),
        "description" => $this->translate("Only enabled delivery services will be available at the checkout."),
        "show_column" => TRUE,
      ],

      "connected_plugin" => [
        "type" => 'varchar',
        "title" => $this->translate("Connected plugin"),
        "description" => $this->translate("Select a plugin which will be used to process the delivery."),
        "enum_values" => $this->deliveryPluginsEnumValues,
        "show_column" => TRUE,
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

  public function tableParams($params) {
    $params['header'] = "
      <p>".$this->translate("Manage delivery services here. Insert only record for each contract with your delivery service provider.")."</p>
    ";

    return $params;
  }

  public function formParams($data, $params) {
    $params["template"] = [
      "columns" => [
        [
          "tabs" => [
            $this->translate("General") => [
              "name",
              "description",
              "logo",
            ],
            $this->translate("Enable / Disable") => [
              "is_enabled",
            ],
            "Plugin" => [
              "connected_plugin",
            ],
          ]
        ],
      ],
    ];

    return $params;
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

  // public function getEnumValues() {
  //   $enumDeliveryServices = [
  //     "" => "Unselected",
  //   ];
  //   foreach ($this->getAll() as $deliveryService) {
  //     if ($deliveryService["is_enabled"] == 1) {
  //       $enumDeliveryServices[$deliveryService["id"]] = $deliveryService["name"];
  //     }
  //   }
  //   return $enumDeliveryServices;
  // }

}