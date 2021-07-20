<?php

namespace ADIOS\Widgets\Products\Models;

class ProductAccessory extends \ADIOS\Core\Model {
  var $sqlName = "products_accessories";
  var $urlBase = "Products/{{ id_product }}/Accessories";

  public function init() {
    $this->languageDictionary["en"] = [
      "Produkt" => "Product",
      "Príslušenstvo" => "Accessory",
      "Príslušenstvo produktu" => "Product accessories",
    ];

    $this->tableTitle = $this->translate("Príslušenstvo produktu");
    $this->formTitleForInserting = $this->translate("Príslušenstvo produktu");
    $this->formTitleForEditing = $this->translate("Príslušenstvo produktu");

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => $this->translate("Produkt"),
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_accessory" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => $this->translate("Príslušenstvo"),
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

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => (int) $params['id_product']];
    return $params;
  }

}