<?php

namespace ADIOS\Widgets\Customers\Models;

class SearchQuery extends \ADIOS\Core\Widget\Model {
  var $sqlName = "customers_search_queries";
  var $urlBase = "Customers/SearchedQueries";

  public function init() {
    $this->tableTitle = $this->translate("Searched queries");
    $this->formTitleForInserting = $this->translate("New searched query");
    $this->formTitleForEditing = $this->translate("Searched query");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer_uid" => [
        "type" => "lookup",
        "title" => $this->translate("Customer UID"),
        "model" => "Widgets/Customers/Models/CustomerUID",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "query" => [
        "type" => "varchar",
        "title" => $this->translate("Searched query"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "target_url" => [
        "type" => "varchar",
        "title" => $this->translate("Target URL"),
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "search_datetime" => [
        "type" => "datetime",
        "title" => $this->translate("Search datetime"),
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