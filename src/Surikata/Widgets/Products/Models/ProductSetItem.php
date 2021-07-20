<?php

namespace ADIOS\Widgets\Products\Models;

class ProductSetItem extends \ADIOS\Core\Model {
  var $sqlName = "products_sets_items";
  var $urlBase = "Produkty/Sety/{{ id_set }}/Items";
  var $tableTitle = "Product set items";
  var $formTitleForInserting = "New product set item";
  var $formTitleForEditing = "Product set item";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_set" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductSet",
        "title" => "Set",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Produkt",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_set = ".(int) $params['id_set'];
    $params["show_controls"] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_set' => (int) $params['id_set']];
    return $params;
  }

}