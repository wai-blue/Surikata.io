<?php

namespace ADIOS\Widgets\Products\Models;

class ProductFeatureAssignment extends \ADIOS\Core\Model {
  var $sqlName = "products_features_assignment";
  var $urlBase = "Produkty/{{ id_product }}/Features";
  var $tableTitle = "Product features";
  var $formTitleForInserting = "New product feature";
  var $formTitleForEditing = "Product feature";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Product",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_feature" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductFeature",
        "title" => "Feature",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "value_text" => [
        'type' => 'text',
        'title' => 'Value: Text',
        'show_column' => TRUE,
      ],

      "value_number" => [
        'type' => 'float',
        'title' => 'Value: Number',
        'show_column' => TRUE,
      ],

      "value_boolean" => [
        'type' => 'boolean',
        'title' => 'Value: Yes/No',
        'show_column' => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => $params['id_product']];
    return $params;
  }

}