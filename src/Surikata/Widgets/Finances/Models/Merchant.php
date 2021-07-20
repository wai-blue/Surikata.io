<?php

namespace ADIOS\Widgets\Finances\Models;

class Merchant extends \ADIOS\Core\Model {
  var $sqlName = "merchants";
  var $lookupSqlValue = "concat({%TABLE%}.company_name)";
  var $urlBase = "Merchants";
  var $tableTitle = "Merchants";
  var $formTitleForInserting = "New merchant";
  var $formTitleForEditing = "Merchant";
  
  public function columns(array $columns = []) {
    return parent::columns([
      "company_name" => [
        "type" => "varchar",
        "title" => "Company name",
      ],

      "street_1" => [
        "type" => "varchar",
        "title" => "Street, 1st line",
      ],

      "street_2" => [
        "type" => "varchar",
        "title" => "Street, 2nd line",
      ],

      "city" => [
        "type" => "varchar",
        "title" => "City",
      ],

      "zip" => [
        "type" => "varchar",
        "title" => "ZIP",
      ],

      "country" => [
        "type" => "varchar",
        "title" => "Country",
      ],

      "company_id" => [
        "type" => "varchar",
        "title" => "Company ID",
      ],

      "company_tax_id" => [
        "type" => "varchar",
        "title" => "Company TAX ID",
      ],

      "company_vat_id" => [
        "type" => "varchar",
        "title" => "Company VAT ID",
      ],

      "email" => [
        "type" => "varchar",
        "title" => "E-mail",
      ],

      "phone" => [
        "type" => "varchar",
        "title" => "Phone number",
      ],

      "www" => [
        "type" => "varchar",
        "title" => "WWW",
      ],

      "iban" => [
        "type" => "varchar",
        "title" => "Bank account IBAN",
      ],

      "description" => [
        'type' => 'text',
        'title' => "Description",
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => 'Logo',
        'show_column' => TRUE,
        "subdir" => "merchant_logos"
      ],

    ]);
  }

}