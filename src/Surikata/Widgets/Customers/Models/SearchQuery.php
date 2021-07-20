<?php

namespace ADIOS\Widgets\Customers\Models;

class SearchQuery extends \ADIOS\Core\Model {
  var $sqlName = "customers_search_queries";
  var $urlBase = "Customers/SearchedQueries";
  var $tableTitle = "Searched queries";
  var $formTitleForInserting = "New searched query";
  var $formTitleForEditing = "Searched query";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer_uid" => [
        "type" => "lookup",
        "title" => "Customer UID",
        "model" => "Widgets/Customers/Models/CustomerUID",
        "show_column" => TRUE,
      ],

      "query" => [
        "type" => "varchar",
        "title" => "Searched query",
        "show_column" => TRUE,
      ],

      "search_datetime" => [
        "type" => "datetime",
        "title" => "Search datetime",
        "show_column" => TRUE,
      ],

    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "query" => [
        "type" => "index",
        "columns" => ["query"],
      ],
      "query___search_datetime" => [
        "type" => "index",
        "columns" => ["query", "search_datetime"],
      ],
    ]);
  }

}