<?php

namespace ADIOS\Widgets\Customers\Models;

class ShoppingCart extends \ADIOS\Core\Model {
  var $sqlName = "shopping_carts";
  var $urlBase = "Customers/ShoppingCarts";
  var $tableTitle = "Shopping carts";
  var $lookupSqlValue = "{%TABLE%}.id";

  public function init() {
    $this->tableTitle = $this->translate("Shopping carts");
    $this->formTitleForInserting = $this->translate("New shopping cart");
    $this->formTitleForEditing = $this->translate("Shopping cart");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer_uid" => [
        "type" => "lookup",
        "title" => $this->translate("Customer UID"),
        "model" => "Widgets/Customers/Models/CustomerUID",
        "show_column" => TRUE,
      ],

      "id_order" => [
        "type" => "lookup",
        "title" => $this->translate("Order"),
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => TRUE,
      ],

    ]);
  }

  public function formParams($data, $params) {
    $params["template"] = [
      "columns" => [
        [
          "tabs" => [
            $this->translate("Header") => [
              "id_customer_uid",
              "id_order",
            ],
            $this->translate("Items") => [
              "action" => "UI/Table",
              "params" => [
                "model"               => "Widgets/Customers/Models/ShoppingCartItem",
                "id_shopping_cart"    => (int) $data['id'],
                "show_add_button"     => FALSE
              ]
            ],
          ],
        ],
      ],
    ];

    $params["show_delete_button"] = FALSE;
    $params["show_save_button"] = FALSE;
    $params["readonly"] = TRUE;

    return $params;
  }

  public function tableParams($params) {
    $params["show_add_button"] = FALSE;

    return $params;
  }

  public function product() {
    return $this->hasOne(\ADIOS\Widgets\Products\Models\Product::class, 'id_product');
  }

  public function getOrCreateCartForCustomerUID($customerUID) {
    $customerUIDlink = $this->adios
      ->getModel("Widgets/Customers/Models/CustomerUID")
      ->getByCustomerUID($customerUID)
    ;

    $cart = $this->where('id_customer_uid', '=', $customerUIDlink['id'])->get();
    if ($cart->isEmpty()) {
      $idCart = $this->insertRow(["id_customer_uid" => $customerUIDlink['id']]);
    } else {
      $idCart = $cart->toArray()[0]['id'];
    }

    return (int) $idCart;
  }

  public function getCartContents($customerUID) {
    $cartItemModel = new \ADIOS\Widgets\Customers\Models\ShoppingCartItem($this->adios);

    $idCart = $this->getOrCreateCartForCustomerUID($customerUID);
    $items = $cartItemModel->getByCartId($idCart);

    // calculate total price
    $priceExclVAT = 0;
    $priceInclVAT = 0;
    $weight = 0;

    foreach ($items as $key => $item) {
      $items[$key]['vat_percent'] = $item['PRODUCT']['vat_percent'];
    }
    
    $items = \ADIOS\Widgets\Finances::calculatePricesForInvoice($items);

    foreach ($items as $key => $item) {
      $priceExclVAT += $items[$key]['PRICES_FOR_INVOICE']['totalPriceExclVAT'];
      $priceInclVAT += $items[$key]['PRICES_FOR_INVOICE']['totalPriceInclVAT'];
      $weight += $item['quantity'] * $item['PRODUCT']['weight'];
    }

    return [
      'items' => $items,
      'summary' => [
        'priceExclVAT' => $priceExclVAT,
        'priceInclVAT' => $priceInclVAT,
        'weight' => $weight,
      ],
    ];
  }

  public function emptyCart($customerUID) {
    $idCart = $this->getOrCreateCartForCustomerUID($customerUID);

    $cartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adios);
    $cartItemModel = new \ADIOS\Widgets\Customers\Models\ShoppingCartItem($this->adios);

    $cartItemModel
      ->where('id_shopping_cart', '=', $idCart)
      ->delete()
    ;

    $cartModel
      ->where('id', '=', $idCart)
      ->delete()
    ;
  }

  public function addProductToCart($customerUID, $idProduct, $qty) {
    $idCart = $this->getOrCreateCartForCustomerUID($customerUID);

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $cartItemModel = new \ADIOS\Widgets\Customers\Models\ShoppingCartItem($this->adios);

    $product = $productModel->getPriceInfoForSingleProduct($idProduct);

    $item = $cartItemModel
      ->where('id_shopping_cart', '=', $idCart)
      ->where('id_product', '=', $idProduct)
    ;

    if ($item->get()->isEmpty()) {
      $cartItemModel->insertRow([
        "id_shopping_cart" => $idCart,
        "id_product" => $idProduct,
        "quantity" => $qty,
        "unit_price" => $product['salePriceExclVAT'],
        "added_on" => date("Y-m-d H:i:s"),
      ]);
    } else {
      $item->update([
        "quantity" => max(0, $item->get()->first()->quantity + $qty),
        "unit_price" => $product['salePriceExclVAT'],
        "updated_on" => date("Y-m-d H:i:s"),
      ]);
    }

    return $item->with('product')->get()->first()->toArray();
  }

  public function updateProductQty($customerUID, $idProduct, $qty) {
    $idCart = $this->getOrCreateCartForCustomerUID($customerUID);

    $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
    $cartItemModel = new \ADIOS\Widgets\Customers\Models\ShoppingCartItem($this->adios);

    $product = $productModel->getPriceInfoForSingleProduct($idProduct);

    $item = $cartItemModel
      ->where('id_shopping_cart', '=', $idCart)
      ->where('id_product', '=', $idProduct)
    ;

    $item->update([
      "quantity" => $qty,
      "unit_price" => $product['salePriceExclVAT'],
      "updated_on" => date("Y-m-d H:i:s"),
    ]);

    return $item->get()->first()->toArray();
  }

  public function removeProductFromCart($customerUID, $idProduct) {
    $idCart = $this->getOrCreateCartForCustomerUID($customerUID);
    $cartItemModel = $this->adios->getModel("Widgets/Customers/Models/ShoppingCartItem");

    if ($idProduct == 0) {
      $cartItem = $cartItemModel
        ->where('id_shopping_cart', '=', $idCart)
      ;
    }
    else {
      $cartItem = $cartItemModel
        ->where('id_shopping_cart', '=', $idCart)
        ->where('id_product', '=', $idProduct)
      ;
    }
    $cartItemDelete = $cartItem->get();
    $cartItem->delete();

    return $cartItemDelete;
  }

}