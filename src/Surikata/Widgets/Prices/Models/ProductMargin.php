<?php

namespace ADIOS\Widgets\Prices\Models;

class ProductMargin extends \ADIOS\Core\Model {
  var $sqlName = "product_margins";
  var $urlBase = "Products/Prices/Margins";
  var $tableTitle = "Product-based margins";
  var $formTitleForEditing = "Product-based margin";
  var $formTitleForInserting = "New product-based margin";

  public function init() {
    $this->languageDictionary["en"] = [
      "Klient" => "Customer",
      "Klient: Kategória" => "Customer: Category",
    ];
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => $this->translate("Klient"),
        "model" => "Widgets/Customers/Models/Customer",
        "show_column" => TRUE,
      ],

      "id_customer_category" => [
        "type" => "lookup",
        "title" => $this->translate("Klient: Kategória"),
        "model" => "Widgets/Customers/Models/CustomerCategory",
        "show_column" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => "Produkt",
        "model" => "Widgets/Products/Models/Product",
        "show_column" => TRUE,
      ],

      "id_product_category" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductCategory",
        "title" => "Produkt: Kategória",
        "show_column" => true
      ],

      "id_brand" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Brand",
        "title" => "Výrobca",
        "show_column" => true
      ],

      "id_supplier" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Supplier",
        "title" => "Dodávateľ",
        "show_column" => true
      ],

      "margin" => [
        "type" => "float",
        "decimals" => 2,
        "title" => "Marža",
        "unit" => "%",
        "show_column" => TRUE,
      ],

    ]);
  }

}
