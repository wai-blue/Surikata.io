<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderState extends \ADIOS\Core\Model {
  var $sqlName = "order_state";
  var $urlBase = "Orders/State";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Name"),
        "show_column" => TRUE,
      ],
      "color" => [
        "type" => "varchar",
        "title" => $this->translate("Color"),
        "show_column" => TRUE,
      ],
      "notes" => [
        "type" => "varchar",
        "title" => $this->translate("Notes"),
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