<?php

namespace ADIOS\Widgets\Products\Models;

class ProductPrice extends \ADIOS\Core\Widget\Model {
  var $sqlName = "product_prices";
  var $urlBase = "Products/Prices";

  public function init() {
    $this->tableTitle = $this->translate("Product price");
    $this->formTitleForInserting = $this->translate("New product price");
    $this->formTitleForEditing = $this->translate("Product price");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
        "show_column" => TRUE,
      ],

      "purchase_price" => [
        "type" => "float",
        "title" => $this->translate("Purchase price excl. VAT"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "recommended_price" => [
        "type" => "float",
        "title" => $this->translate("Recommended price excl. VAT"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Products\/(\d+)\/Prices$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Products/Models/ProductPrice",
          "id_product" => '$1',
        ]
      ],
    ]);
  }

  public function formParams($data, $params) {

    $params["template"] = [
      "columns" => [
        [
          "rows" => [
            "id_product",
            "purchase_price",
            "recommended_price",
            "is_including_vat",
          ],
        ],
      ],
    ];

    return $params;
  }

  public function tableParams($params) {
    $idProduct = (int) $params['id_product'];
    
    if ($idProduct > 0) {
      $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
      $params['show_search_button'] = FALSE;
      $params['show_controls'] = FALSE;
      $params['show_filter'] = FALSE;
      $params['title'] = " ";
    }

    return $params;
  }

}
