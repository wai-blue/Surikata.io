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
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "query" => [
        "type" => "varchar",
        "title" => "Searched query",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "target_url" => [
        "type" => "varchar",
        "title" => "Target URL",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "search_datetime" => [
        "type" => "datetime",
        "title" => "Search datetime",
        "readonly" => TRUE,
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

  public function tableParams($params) {
    $params['show_add_button'] = FALSE;

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function formParams($data, $params) {
    $params['readonly'] = TRUE;
    return $params;
  }

}