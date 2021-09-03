<?php

namespace ADIOS\Widgets\Shipping\Models;

class PaymentService extends \ADIOS\Core\Model {
  var $sqlName = "payment_services";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Shipping/PaymentServices";
  var $tableTitle = "Payment services";
  var $formTitleForInserting = "New payment service";
  var $formTitleForEditing = "Payment service";

  var $paymentPluginsEnumValues = [];

  public function init() {
    $this->paymentPluginsEnumValues = [
      "" => "-- Select a payment plugin --",
    ];

    foreach ($this->adios->websiteRenderer->getPaymentPlugins() as $paymentPlugin) {
      $this->paymentPluginsEnumValues[$paymentPlugin->name] = $paymentPlugin->name;
    }
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Payment name"),
        'required' => TRUE,
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'varchar',
        'title' => $this->translate("Payment description"),
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => $this->translate("Payment logo"),
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
        'title' => $this->translate("Connected payment plugin"),
        "enum_values" => $this->paymentPluginsEnumValues,
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

  public function getAll(string $keyBy = "id") {
    return self::get()->toArray();
  }

}