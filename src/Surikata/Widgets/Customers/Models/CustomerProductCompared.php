<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerProductCompared extends \ADIOS\Core\Model {
  var $sqlName = "customers_products_compared";
  var $tableTitle = "Porovnávané produkty";
  var $formTitleForInserting = "Porovnávané produkty";
  var $formTitleForEditing = "Porovnávané produkty";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer_uid" => [
        "type" => "lookup",
        "title" => "Customer UID",
        "model" => "Widgets/Customers/Models/CustomerUID",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => FALSE,
      ],

      "id_product_1" => [
        "type" => "lookup",
        "title" => "Produkt #1",
        "model" => "Widgets/Products/Models/Product",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "id_product_2" => [
        "type" => "lookup",
        "title" => "Produkt #2",
        "model" => "Widgets/Products/Models/Product",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "comparison_datetime" => [
        "type" => "datetime",
        "title" => "Comparison datetime",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

    ]);
  }

  public function tableParams($params) {
    $params["where"] = $this->getFullTableSQLName().".id_customer = ".(int) $params['id_customer'];

    if ($params['id_customer']) {
      $params["onclick"] = " ";
      $params["show_title"] = FALSE;
    }
    return $params;
  }

}