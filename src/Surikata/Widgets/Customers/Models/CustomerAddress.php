<?php

namespace ADIOS\Widgets\Customers\Models;

use ADIOS\Widgets\Customers\Exceptions\UnknownAccount;

class CustomerAddress extends \ADIOS\Core\Model {
  var $sqlName = "customers_addresses";
  var $urlBase = "Customers/{{ id_customer }}/Addresses";
  var $lookupSqlValue = "
    concat(
      ifnull({%TABLE%}.del_given_name, ''), ' ',
      ifnull({%TABLE%}.del_family_name, ''), ' ',
      ifnull({%TABLE%}.del_company_name, ''), ', ',
      ifnull({%TABLE%}.del_city, '')
    )
  ";

  public function init() {
    $this->tableTitle = $this->translate("Customer addresses");
    $this->formTitleForInserting = $this->translate("New customer address");
    $this->formTitleForEditing = $this->translate("Customer address");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => $this->translate("Customer"),
        "model" => "Widgets/Customers/Models/Customer",
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "hash" => [
        "type" => "varchar",
        "title" => $this->translate("Hash"),
      ],

      "del_given_name" => [
        "type" => "varchar",
        "title" => $this->translate("Given name"),
      ],

      "del_family_name" => [
        "type" => "varchar",
        "title" => $this->translate("Family name"),
        "show_column" => TRUE,
      ],

      "del_company_name" => [
        "type" => "varchar",
        "title" => $this->translate("Company name"),
        "show_column" => TRUE,
      ],

      "del_street_1" => [
        "type" => "varchar",
        "title" => $this->translate("Street, 1st line"),
        "show_column" => TRUE,
      ],

      "del_street_2" => [
        "type" => "varchar",
        "title" => $this->translate("Street, 2nd line"),
      ],

      "del_floor" => [
        "type" => "varchar",
        "title" => $this->translate("Floor"),
      ],

      "del_city" => [
        "type" => "varchar",
        "title" => $this->translate("City"),
        "show_column" => TRUE,
      ],

      "del_zip" => [
        "type" => "varchar",
        "title" => $this->translate("ZIP"),
      ],

      "del_region" => [
        "type" => "varchar",
        "title" => $this->translate("Region"),
      ],

      "del_country" => [
        "type" => "varchar",
        "title" => $this->translate("Country"),
      ],



      "phone_number" => [
        "type" => "varchar",
        "title" => $this->translate("Contact: ").$this->translate("Phone number"),
        "show_column" => TRUE,
      ],

      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Contact: ").$this->translate("Email"),
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
            $this->translate("Customer") => [
              "id_customer",
              "phone_number",
              "email",
            ],
            $this->translate("Delivery address") => [
              "del_given_name",
              "del_family_name",
              "del_company_name",
              "del_street_1",
              "del_street_2",
              "del_city",
              "del_zip",
              "del_region",
              "del_country",
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

  public function belongsToCustomer($idCustomer, $idAddress) {

    $address = $this->getById($idAddress);
    return ((int)$address["id_customer"] === $idCustomer);

  }

  public function saveAddress($idCustomer, $data) {
    // REVIEW: nutne zvalidovat, ci $data['idAddress'] patri $idCustomer, vid belongsToCustomer()

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

      if (!$this->belongsToCustomer($idCustomer, $data["idAddress"])) {
        throw new UnknownAccount();
      }

      self::where('id', $data["idAddress"])->update($addressData);
      $item = $addressData;
      $item["id"] = $data["idAddress"];
    }

    return $item['id'];
  }

  public function removeAddress($idCustomer, $idAddress) {

    if (!$this->belongsToCustomer($idCustomer, $idAddress)) {
      throw new UnknownAccount();
    }
    
    $removeAddress = self::where('id', $idAddress)
      ->where('id_customer', $idCustomer)
    ;

    if (!$removeAddress->delete()) {
      throw new \ADIOS\Widgets\Customers\Exceptions\RemoveAddressUnknownError();
    }
  }

}