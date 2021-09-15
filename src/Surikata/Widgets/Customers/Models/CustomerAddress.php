<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerAddress extends \ADIOS\Core\Model {
  var $sqlName = "customers_addresses";
  var $urlBase = "Customers/{{ id_customer }}/Addresses";
  var $tableTitle = "Customer addresses";
  var $formTitleForInserting = "New customer address";
  var $formTitleForEditing = "Customer address";
  var $lookupSqlValue = "
    concat(
      ifnull({%TABLE%}.inv_given_name, ''), ' ',
      ifnull({%TABLE%}.inv_family_name, ''), ' ',
      ifnull({%TABLE%}.inv_company_name, ''), ', ',
      ifnull({%TABLE%}.inv_city, '')
    )
  ";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => "Customer",
        "model" => "Widgets/Customers/Models/Customer",
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "hash" => [
        "type" => "varchar",
        "title" => "Hash",
      ],



      "del_given_name" => [
        "type" => "varchar",
        "title" => "Delivery: Given Name",
      ],

      "del_family_name" => [
        "type" => "varchar",
        "title" => "Delivery: Family Name",
        "show_column" => TRUE,
      ],

      "del_company_name" => [
        "type" => "varchar",
        "title" => "Delivery: Company Name",
        "show_column" => TRUE,
      ],

      "del_street_1" => [
        "type" => "varchar",
        "title" => "Delivery: Street, 1st line",
        "show_column" => TRUE,
      ],

      "del_street_2" => [
        "type" => "varchar",
        "title" => "Delivery: Street, 2nd line",
      ],

      "del_floor" => [
        "type" => "varchar",
        "title" => "Delivery: Floor",
      ],

      "del_city" => [
        "type" => "varchar",
        "title" => "Delivery: City",
        "show_column" => TRUE,
      ],

      "del_zip" => [
        "type" => "varchar",
        "title" => "Delivery: ZIP",
      ],

      "del_region" => [
        "type" => "varchar",
        "title" => "Delivery: Region",
      ],

      "del_country" => [
        "type" => "varchar",
        "title" => "Delivery: Country",
      ],



      "inv_given_name" => [
        "type" => "varchar",
        "title" => "Billing: Given Name",
      ],

      "inv_family_name" => [
        "type" => "varchar",
        "title" => "Billing: Family Name",
        "show_column" => TRUE,
      ],

      "inv_company_name" => [
        "type" => "varchar",
        "title" => "Billing: Company Name",
        "show_column" => TRUE,
      ],

      "inv_street_1" => [
        "type" => "varchar",
        "title" => "Billing: Street, 1st line",
        "show_column" => TRUE,
      ],

      "inv_street_2" => [
        "type" => "varchar",
        "title" => "Billing: Street, 2nd line",
      ],

      "inv_floor" => [
        "type" => "varchar",
        "title" => "Billing: Floor",
      ],

      "inv_city" => [
        "type" => "varchar",
        "title" => "Billing: City",
        "show_column" => TRUE,
      ],

      "inv_zip" => [
        "type" => "varchar",
        "title" => "Billing: ZIP",
      ],

      "inv_region" => [
        "type" => "varchar",
        "title" => "Billing: Region",
      ],

      "inv_country" => [
        "type" => "varchar",
        "title" => "Billing: Country",
      ],



      "phone_number" => [
        "type" => "varchar",
        "title" => "Contact: Phone number",
        "show_column" => TRUE,
      ],

      "email" => [
        "type" => "varchar",
        "title" => "Contact: Email",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "hash" => [
        "type" => "index",
        "columns" => ["hash"],
      ],
    ]);
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_customer" => $params["id_customer"]];

    $params["template"] = [
      "columns" => [
        [
          "tabs" => [
            "Customer" => [
              "id_customer",
              "phone_number",
              "email",
            ],
            "Billing address" => [
              "inv_family_name",
              "inv_given_name",
              "inv_company_name",
              "inv_street_1",
              "inv_street_12",
              "inv_city",
              "inv_zip",
            ],
            "Delivery address" => [
              "del_family_name",
              "del_given_name",
              "del_company_name",
              "del_street_1",
              "del_street_12",
              "del_city",
              "del_zip",
            ],
          ],
        ],
      ],
    ];

    return $params;
  }

  public function tableParams($params) {
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";
    $params['where'] = "`{$this->table}`.`id_customer` = ".(int) $params['id_customer'];
    return $params;
  }

  public function saveAddress($idCustomer, $data) {
    $addressData = ["id_customer" => $idCustomer];

    foreach ($this->columnNames() as $colName) {
      if ($colName == "id_customer") continue;
      
      if (empty($data[$colName])) {
        $addressData[$colName] = "";
      } else {
        $addressData[$colName] = $data[$colName];
      }
    }

    $addressData['hash'] = md5(json_encode($addressData));

    if ($data["idAddress"] == 0) {
      $item = $this->firstOrCreate($addressData);
    }
    else {
      self::where('id', $data["idAddress"])->update($addressData);
      $item = $addressData;
      $item["id"] = $data["idAddress"];
    }

    return $item['id'];
  }

  public function removeAddress($idCustomer, $idAddress) {
    $removeAddress = self::where('id', $idAddress)
      ->where('id_customer', $idCustomer)
    ;

    if (!$removeAddress->delete()) {
      throw new \ADIOS\Widgets\Customers\Exceptions\RemoveAddressUnknownError();
    }
  }

}