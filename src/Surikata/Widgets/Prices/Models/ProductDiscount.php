<?php

namespace ADIOS\Widgets\Prices\Models;

class ProductDiscount extends \ADIOS\Core\Model {
  var $sqlName = "product_discounts";
  var $urlBase = "Products/Prices/Discounts";
  var $tableTitle = "Product-based discounts";
  var $formTitleForEditing = "Product-based discount";
  var $formTitleForInserting = "New product-based discount";

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
        "title" => "Customer",
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
        "title" => "Product",
        "model" => "Widgets/Products/Models/Product",
        "show_column" => TRUE,
      ],

      "id_product_category" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductCategory",
        "title" => "Product: Category",
        "show_column" => true
      ],

      "id_brand" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Brand",
        "title" => "Brand",
        "show_column" => true
      ],

      "id_supplier" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Supplier",
        "title" => "Supplier",
        "show_column" => true
      ],

      "discount_percentage" => [
        "type" => "float",
        "title" => "Discount",
        "unit" => "%",
        "show_column" => TRUE,
      ],

    ]);
  }

}
