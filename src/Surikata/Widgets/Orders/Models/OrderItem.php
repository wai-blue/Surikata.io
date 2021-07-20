<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderItem extends \ADIOS\Core\Model {
  var $sqlName = "orders_items";
  var $urlBase = "Orders/{{ id_order }}/Items";
  var $tableTitle = " ";
  var $formTitleForInserting = "New order item";
  var $formTitleForEditing = "Order items";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_order" => [
        "type" => "lookup",
        "title" => "Order",
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => FALSE,
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => "Product",
        "model" => "Widgets/Products/Models/Product",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "quantity" => [
        "type" => "float",
        "title" => "Quantity",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "id_delivery_unit" => [
        "type" => "lookup",
        "title" => $this->translate("Delivery unit"),
        "model" => "Widgets/Settings/Models/Unit",
        "show_column" => TRUE,
      ],

      "unit_price" => [
        "type" => "float",
        "title" => "Unit price",
        "unit" => $this->adios->locale->currencySymbol(),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "vat_percent" => [
        "type" => "int",
        "title" => "VAT",
        "unit" => "%",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "`{$this->table}`.`id_order` = ".(int) $params['id_order'];
    $params["show_controls"] = FALSE;
    $params["show_search_button"] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_order" => $params["id_order"]];
    return $params;
  }

}