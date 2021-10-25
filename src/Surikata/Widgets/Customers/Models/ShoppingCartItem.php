<?php

namespace ADIOS\Widgets\Customers\Models;

class ShoppingCartItem extends \ADIOS\Core\Model {
  var $sqlName = "shopping_carts_items";
  var $urlBase = "ShoppingCarts/{{ id_shopping_cart }}/Items";
  var $tableTitle = " ";
  var $formTitleForInserting = "New shopping cart item";
  var $formTitleForEditing = "Shopping cart item";

  protected $fillable = ['quantity'];
  
  public function columns(array $columns = []) {
    $productModel = $this->adios->getModel("Widgets/Products/Models/Product");
    $unitModel = $this->adios->getModel("Widgets/Settings/Models/Unit");

    return parent::columns([
      "id_shopping_cart" => [
        "type" => "lookup",
        "title" => $this->translate("Order"),
        "model" => "Widgets/Customers/Models/ShoppingCart",
        "show_column" => FALSE,
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "quantity" => [
        "type" => "float",
        "title" => $this->translate("Quantity"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "unit_price" => [
        "type" => "float",
        "sql_data_type" => "decimal",
        "decimals" => 4,
        "title" => $this->translate("Unit price excl. VAT"),
        "unit" => $this->adios->locale->currencySymbol(),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "added_on" => [
        "type" => "datetime",
        "title" => $this->translate("Added on"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "updated_on" => [
        "type" => "datetime",
        "title" => $this->translate("Updated on"),
        "show_column" => TRUE,
      ],

      "virt_delivery_unit" => [
        "type" => "varchar",
        "virtual" => TRUE,
        "sql" => "
          select
            ".$unitModel->lookupSqlValue("u")."
          from `{$productModel->table}` p
          left join {$unitModel->table} u on u.id = p.id_delivery_unit
          where
            p.id = `{$this->table}`.id
        ",
        "title" => "Delivery unit",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function product() {
    return $this->hasOne(\ADIOS\Widgets\Products\Models\Product::class, "id", "id_product");
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_shopping_cart = ".(int) $params['id_shopping_cart'];
    $params["show_controls"] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_shopping_cart" => $params["id_shopping_cart"]];
    return $params;
  }

  public function getByCartId($idCart) {
    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);

    $items = $this
      ->with('product.unit')
      ->where('id_shopping_cart', '=', $idCart)
      ->get()
      ->toArray()
    ;

    foreach (array_keys($items) as $key) {
      $items[$key]['PRODUCT'] = $productModel->getDetailedInfoForSingleProduct($items[$key]['product']);
      unset($items[$key]['product']);
    }

    return $items;
  }
}