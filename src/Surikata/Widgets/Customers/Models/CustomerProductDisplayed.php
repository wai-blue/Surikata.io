<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerProductDisplayed extends \ADIOS\Core\Model {
  var $sqlName = "customers_products_displayed";
  var $tableTitle = "Produkty navštívené klientom";
  var $formTitleForInserting = "Nový produkt navštívený klientom";
  var $formTitleForEditing = "Produkt navštívený klientom";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer_uid" => [
        "type" => "lookup",
        "title" => "Klient UID",
        "model" => "Widgets/Customers/Models/CustomerUID",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => FALSE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => "Produkt",
        "model" => "Widgets/Products/Models/Product",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "display_datetime" => [
        "type" => "datetime",
        "title" => "Display datetime",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_customer = ".(int) $params['id_customer'];

    if ($params['id_customer']) {
      $params["onclick"] = " ";
      $params["show_title"] = FALSE;
    }
    return $params;
  }

  public function logActivityByCustomerUID($customerUID, $idProduct) {
    $customerUIDlink = $this->adios
      ->getModel("Widgets/Customers/Models/CustomerUID")
      ->getByCustomerUID($customerUID)
    ;

    $this->insertRow([
      "id_customer_uid" => $customerUIDlink['id'],
      "id_product" => $idProduct,
      "display_datetime" => date("Y-m-d H:i:s"),
    ]);
  }

}