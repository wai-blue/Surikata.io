<?php

namespace ADIOS\Widgets\Orders\Models;

use ADIOS\Core\Models\User;

class OrderHistory extends \ADIOS\Core\Widget\Model {
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

      "state" => [
        "type" => "int",
        "enum_values" => (new \ADIOS\Widgets\Orders\Models\Order($this->adios))->enumOrderStates,
        "title" => $this->translate("State"),
        "show_column" => true
      ],

      "event_time" => [
        "type" => "datetime",
        "title" => $this->translate("Event time"),
        "show_column" => true
      ],

      //"notes" => [
      //  "type" => "varchar",
      //  "title" => $this->translate("Notes"),
      //  "show_column" => TRUE,
      //],

      "user" => [
        "type" => "lookup",
        "title" => $this->translate("User"),
        "model" => "Core/Models/User",
        "show_column" => TRUE,
        "readonly" => TRUE,
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

  public function tableCellHTMLFormatter($data) {
    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);
    $orderModel->init();

    if ($data['column'] == "state") {
      return $orderModel->enumOrderStates[(int)$data['row']['state']];
    }
    if ($data['column'] == "user") {
      if ($data['row']['user'] == 0) {
        return "Automatic change";
      }
      else {
        return (new User($this->adios))->getById($data['row']['user'])["name"];
      }
    }
  }

  public function tableCellCSSFormatter($data) {

    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);
    $orderModel->init();

    if ($data['column'] == "state") {
      return "border-left: 10px solid {$orderModel->enumOrderStateColors[(int)$data['row']['state']]};";
    }
  }

}