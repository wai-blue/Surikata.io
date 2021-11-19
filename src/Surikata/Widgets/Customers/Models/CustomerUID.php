<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerUID extends \ADIOS\Core\Widget\Model {
  var $sqlName = "customers_uid";
  var $urlBase = "Customers/{{ id_customer }}/UID";
  var $tableTitle = "UID klienta";
  var $lookupSqlValue = "{%TABLE%}.uid";
  var $formTitleForInserting = "UID klienta";
  var $formTitleForEditing = "UID klienta";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => "Klient",
        "model" => "Widgets/Customers/Models/Customer",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "uid" => [
        "type" => "varchar",
        "title" => "UID",
        "show_column" => TRUE,
      ],

      "cookie_consent" => [
        "type" => "varchar",
        "title" => "Cookie consent",
        "show_column" => TRUE,
        "readonly" => TRUE,
      ],
      "cookie_date" => [
        "type" => "datetime",
        "title" => "Cookie consent date",
        "show_column" => TRUE,
        "readonly" => TRUE,
      ],
    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "uid" => [
        "type" => "index",
        "columns" => ["uid"],
      ],
    ]);
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_customer" => $params["id_customer"]];

    return $params;
  }

  public function setCookieConsent($uid, $consent) {
    $cookie = [];
    $cookie["cookie_consent"] = $consent;
    $cookie["cookie_date"] = date("Y-m-d H:i:s");

    self::where('uid', $uid)->update($cookie);
  }

  public function getByCustomerUID($customerUID) {
    $item = self::firstOrCreate(['uid' => $customerUID]);
    return $item->toArray();
  }

}