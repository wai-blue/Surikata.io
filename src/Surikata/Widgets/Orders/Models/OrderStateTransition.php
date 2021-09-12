<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderStateTransition extends \ADIOS\Core\Model {
  var $sqlName = "order_state_transition";
  var $urlBase = "Orders/State/Transition";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_original_state" => [
        "type" => "lookup",
        "title" => $this->translate("Original state"),
        "model" => "Widgets/Orders/Models/OrderState",
        "show_column" => TRUE,
      ],
      "id_new_state" => [
        "type" => "lookup",
        "title" => $this->translate("New state"),
        "model" => "Widgets/Orders/Models/OrderState",
        "show_column" => TRUE,
      ],
      "is_initial" => [
        "type" => "bool",
        "title" => $this->translate("Is initial"),
        "show_column" => true
      ],
      "action" => [
        "type" => "varchar",
        "title" => $this->translate("Action"),
        "show_column" => TRUE,
      ],
    ]);
  }
  
  public function tableParams($params) {
    //$params["readonly"] = TRUE;
    $params["show_add_button"] = TRUE;
    //$params["where"] = "`{$this->table}`.`id_order` = ".(int) $params['id_order'];
    $params["show_title"] = TRUE;
    $params["show_controls"] = TRUE;

    return $params;
  }

  public function tableRowCSSFormatter($data) {
    return "background-color: {$data['row']['color']}99;";
  }

  public function tableCellCSSFormatter($data) {
    if ($data['column'] == "name") {
      return "font-weight: bold";
    }
  }

}