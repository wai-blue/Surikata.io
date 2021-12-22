<?php

namespace ADIOS\Widgets\Finances\Models;

class Merchant extends \ADIOS\Core\Widget\Model {
  var $sqlName = "merchants";
  var $lookupSqlValue = "concat({%TABLE%}.company_name)";
  var $urlBase = "Merchants";

  public function init() {
    $this->tableTitle = $this->translate("Merchants");
    $this->formTitleForInserting = $this->translate("New merchant");
    $this->formTitleForEditing = $this->translate("Merchant");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "company_name" => [
        "type" => "varchar",
        "title" => $this->translate("Company name"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "street_1" => [
        "type" => "varchar",
        "title" => $this->translate("Street, 1st line"),
        "show_column" => TRUE,
      ],

      "street_2" => [
        "type" => "varchar",
        "title" => $this->translate("Street, 2nd line"),
      ],

      "city" => [
        "type" => "varchar",
        "title" => $this->translate("City"),
      ],

      "zip" => [
        "type" => "varchar",
        "title" => $this->translate("ZIP"),
      ],

      "country" => [
        "type" => "varchar",
        "title" => $this->translate("Country"),
      ],

      "company_id" => [
        "type" => "varchar",
        "title" => $this->translate("Company ID"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "company_tax_id" => [
        "type" => "varchar",
        "title" => $this->translate("Company TAX ID"),
      ],

      "company_vat_id" => [
        "type" => "varchar",
        "title" => $this->translate("Company VAT ID"),
      ],

      "email" => [
        "type" => "varchar",
        "title" => $this->translate("E-mail"),
        "show_column" => TRUE,
      ],

      "phone" => [
        "type" => "varchar",
        "title" => $this->translate("Phone number"),
        "show_column" => TRUE,
      ],

      "www" => [
        "type" => "varchar",
        "title" => $this->translate("WWW"),
      ],

      "iban" => [
        "type" => "varchar",
        "title" => $this->translate("Bank account IBAN"),
        "show_column" => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' => $this->translate("Description"),
      ],

      "logo" => [
        'type' => 'image',
        'title' => 'Logo',
        "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
        "subdir" => $this->translate("merchant_logos")
      ],

    ]);
  }

}