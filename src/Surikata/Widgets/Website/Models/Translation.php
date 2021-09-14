<?php

namespace ADIOS\Widgets\Website\Models;

class Translation extends \ADIOS\Core\Model {
  var $sqlName = "web_translations";
  var $urlBase = "Website/Translations";
  var $tableTitle = "Translations";
  var $formTitleForInserting = "New translation";
  var $formTitleForEditing = "Translation";

  public function columns($columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
        "show_column" => FALSE,
      ],

      "context" => [
        "type" => "varchar",
        "title" => "Context",
        "show_column" => TRUE,
      ],

      "original" => [
        "type" => "varchar",
        "title" => "Original string",
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      // "language_index" => [
      //   "type" => "int",
      //   "title" => "Language",
      //   "enum_values" => $this->adios->config['widgets']['Website']['domainLanguages'],
      //   "show_column" => TRUE,
      //   "required" => TRUE,
      // ],

      "translated" => [
        "type" => "text",
        "title" => "Translated string",
        "show_column" => TRUE,
        "required" => TRUE,
      ],
    ]);
  }

  public function indexes($indexes = []) {
    return parent::indexes([
      "domain___context___original" => [
        "type" => "index",
        "columns" => ["domain", "context", "original"],
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Website\/(.+)\/Translations$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => $this->name,
          "domain" => '$1',
        ]
      ],
    ]);
  }

  public function tableParams($params) {
    $domain = $params['domain'];
    $domains = $this->adios->config['widgets']['Website']['domains'];
    if (!in_array($domain, array_keys($domains))) {
      $domain = "";
    }

    $params["where"] = $this->getFullTableSQLName().".`domain` = '{$domain}'";

    return $params;
  }

  public function loadCache() {
    $cache = [];
    $all = $this->get()->toArray();

    foreach ($all as $t) {
      $cache[$t["domain"]][$t["context"]][$t["original"]] = $t["translated"];
    }

    return $cache;

  }

}