<?php

namespace ADIOS\Widgets\Website\Models;

class WebTranslation extends \ADIOS\Core\Widget\Model {
  var $sqlName = "web_translations";
  var $urlBase = "Website/{{ domainName }}/Translations";

  public function init() {
    $this->tableTitle = $this->translate("Translations");
    $this->formTitleForInserting = $this->translate("New translation");
    $this->formTitleForEditing = $this->translate("Translation");
  }

  public function columns($columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => TRUE,
        "readonly" => TRUE,
        "show_column" => FALSE,
        "enum_values" => $this->adios->getEnumValuesForListOfDomains(),
      ],

      "context" => [
        "type" => "varchar",
        "title" => $this->translate("Context"),
        "show_column" => TRUE,
      ],

      "original" => [
        "type" => "varchar",
        "title" => $this->translate("Original string"),
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
        "title" => $this->translate("Translated string"),
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
    $params["title"] = "{$params['domainName']} &raquo; ".$this->translate("Translations");
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