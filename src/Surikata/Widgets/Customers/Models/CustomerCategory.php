<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerCategory extends \ADIOS\Core\Model {
  var $sqlName = "customers_categories";
  var $lookupSqlValue = "concat({%TABLE%}.name, ' [', {%TABLE%}.code, ']')";
  var $urlBase = "Customers/Categories";
  var $tableTitle = "Customer categories";
  var $formTitleForInserting = "New customer category";
  var $formTitleForEditing = "Customer category";

  public function init() {
    $this->languageDictionary["sk"] = [
      "Short code" => "Kód kategórie",
      "Full category name" => "Celý názov kategórie",
      "Customer categories" => "Kategórie zákazníkov"
    ];

    $this->tableTitle = $this->translate("Customer categories");
  }

  public function columns(array $columns = []) {
    return parent::columns([

      "code" => [
        "type" => "varchar",
        "title" => $this->translate("Short code"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Full category name"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      // "order_index" => [
      //   "type" => "int",
      //   "title" => "Order index",
      //   "show_column" => TRUE,
      // ],

      // "tree_left_index" => [
      //   "type" => "int",
      //   "title" => "Tree left index",
      //   "readonly" => TRUE,
      //   "show_column" => TRUE,
      // ],

      // "tree_right_index" => [
      //   "type" => "int",
      //   "title" => "Tree right index",
      //   "readonly" => TRUE,
      //   "show_column" => TRUE,
      // ],

    ]);
  }

  // public function routing($columns = []) {
  //   return parent::routing([
  //     '/^Customers\/Categories\/(\d+)\/Add$/' => [
  //       "action" => "UI/Form",
  //       "params" => [
  //         "model" => "Widgets/Customers/Models/CustomerCategory",
  //         "id_parent" => '$1',
  //       ]
  //     ],
  //   ]);
  // }
}