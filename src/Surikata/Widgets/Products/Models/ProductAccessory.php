<?php

namespace ADIOS\Widgets\Products\Models;

class ProductAccessory extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_accessories";
  var $urlBase = "Products/{{ id_product }}/Accessories";

  public function init() {
    $this->tableTitle = $this->translate("Product accessories");
    $this->formTitleForInserting = $this->translate("Product accessories");
    $this->formTitleForEditing = $this->translate("Product accessories");

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => $this->translate("Product"),
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_accessory" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => $this->translate("Accessory"),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";
    $params['header'] = $this->translate("Product accessories are separate products with their identification number but are closely related to this product. Accessories can be offered in the product's detail page as separate links to another products.");

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => (int) $params['id_product']];
    return $params;
  }

}