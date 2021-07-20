<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderHistory extends \ADIOS\Core\Model {
  var $sqlName = "orders_history";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_order" => [
        "type" => "lookup",
        "title" => $this->translate("Order"),
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => FALSE,
        "readonly" => TRUE,
      ],

      "event_time" => [
        "type" => "date",
        "title" => $this->translate("Event time"),
        "show_column" => true
      ],

      "state" => [
        "type" => "int",
        "enum_values" => $this->enumOrderStates,
        "title" => $this->translate("State"),
        "show_column" => true
      ],

      "notes" => [
        "type" => "varchar",
        "title" => $this->translate("Notes"),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return [
      '/^Orders\/(\d+)\/Historia$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "OrderHistoria",
        ]
      ],
    ];
  }
  
  public function tableParams($params) {
    $params["readonly"] = TRUE;
    $params["show_add_button"] = FALSE;
    $params["where"] = "`{$this->table}`.`id_order` = ".(int) $params['id_order'];
    $params["show_title"] = FALSE;
    $params["show_controls"] = FALSE;
    return $params;
  }

}