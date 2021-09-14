<?php

namespace ADIOS\Widgets\Products\Models;

class ProductDiscount extends \ADIOS\Core\Model {
  var $sqlName = "product_price_discounts";
  var $urlBase = "Products/Prices/Discounts";
  var $tableTitle = "Product-based discounts";
  var $formTitleForEditing = "Product price discount";
  var $formTitleForInserting = "New product price discount";

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

      "discount_percentage" => [
        "type" => "float",
        "title" => $this->translate("Discount"),
        "unit" => "%",
        "show_column" => TRUE,
      ],

    ]);
  }

}
