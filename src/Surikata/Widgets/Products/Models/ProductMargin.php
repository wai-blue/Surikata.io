<?php

namespace ADIOS\Widgets\Products\Models;

class ProductMargin extends \ADIOS\Core\Model {
  var $sqlName = "product_price_margins";
  var $urlBase = "Products/Prices/Margins";
  var $tableTitle = "Product price margins";
  var $formTitleForEditing = "Product price margin";
  var $formTitleForInserting = "New product price margin";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => $this->translate("Customer"),
        "model" => "Widgets/Customers/Models/Customer",
        "show_column" => TRUE,
      ],

      "id_customer_category" => [
        "type" => "lookup",
        "title" => $this->translate("Customer: Category"),
        "model" => "Widgets/Customers/Models/CustomerCategory",
        "show_column" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
        "show_column" => TRUE,
      ],

      "id_product_category" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductCategory",
        "title" => $this->translate("Product: Category"),
        "show_column" => TRUE,
      ],

      "id_brand" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Brand",
        "title" => $this->translate("Brand"),
        "show_column" => TRUE,
      ],

      "id_supplier" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Supplier",
        "title" => $this->translate("Supplier"),
        "show_column" => TRUE,
      ],

      "margin" => [
        "type" => "float",
        "decimals" => 2,
        "title" => $this->translate("Margin"),
        "unit" => "%",
        "show_column" => TRUE,
      ],

    ]);
  }

  public function formParams($data, $params) {
    $params["template"] = [
      "columns" => [
        [
          "tabs" => [
            "Product" => [
              "id_product",
            ],
            "Product category" => [
              "id_product_category",
            ],
            "Customer" => [
              "id_customer",
            ],
            "Customer category" => [
              "id_customer_category",
            ],
            "Brand" => [
              "id_brand",
            ],
            "Supplier" => [
              "id_supplier",
            ],
            "Margin [%]" => [
              "margin",
            ],
          ],
        ],
      ],
    ];
    
    return $params;
  }
}
