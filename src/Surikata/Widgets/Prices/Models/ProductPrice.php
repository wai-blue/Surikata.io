<?php

namespace ADIOS\Widgets\Prices\Models;

class ProductPrice extends \ADIOS\Core\Model {
  var $sqlName = "prices_purchase";
  var $urlBase = "Products/Prices";
  var $tableTitle = "Purchase pricelist";
  var $formTitleForInserting = "New product purchase price";
  var $formTitleForEditing = "Product purchase price";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
        "show_column" => TRUE,
      ],

      "price_excl_vat" => [
        "type" => "float",
        "title" => $this->translate("Purchase price excl. VAT"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function formParams($data, $params) {
    
    if ($data['id'] <= 0) {
      $params["template"] = [
        "columns" => [
          [
            "rows" => [
              "id_product",
              "price_excl_vat",
            ],
          ],
        ],
      ];
    } else {
      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-9 pl-0",
            "tabs" => [
              "Základná cena" => [
                "id_product",
                "price_excl_vat",
              ],
            ],
          ],
        ],
      ];
    }

    return $params;
  }

}
