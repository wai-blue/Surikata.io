<?php

namespace ADIOS\Widgets\Website\Models;

class WebTranslation extends \ADIOS\Core\Model {
  var $sqlName = "web_translations";
  var $urlBase = "Website/{{ domainName }}/Translations";
  var $tableTitle = "Translations";
  var $formTitleForInserting = "New translation";
  var $formTitleForEditing = "Translation";

  public function columns($columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
        "readonly" => TRUE,
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
      [
        "type" => "index",
        "columns" => ["domain"],
      ],
      [
        "type" => "index",
        "columns" => ["domain", "context", "original"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["title"] = "{$params['domainName']} &raquo; Translations";
    $params["where"] = $this->getFullTableSQLName().".`domain` = '{$params['domainName']}'";

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