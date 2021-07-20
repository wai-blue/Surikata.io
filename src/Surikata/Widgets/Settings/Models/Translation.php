<?php

namespace ADIOS\Widgets\Settings\Models;

class Translation extends \ADIOS\Core\Model {
  var $sqlName = "translations";
  var $urlBase = "Settings/Translations";
  var $tableTitle = "Translations";
  var $formTitleForInserting = "New translation";
  var $formTitleForEditing = "Translation";

  public function columns($columns = []) {
    $schemaForColumnTranslated = ["properties" => []];

    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($this->adios->config['widgets']['Website']['domains'] as $domain => $domainInfo) {
      $schemaForColumnTranslated["properties"][$domain] = [
        "type" => "string",
        "title" => $domainLanguages[$domainInfo["languageIndex"]],
      ];
    }

    return parent::columns([
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

      "translated" => [
        "type" => "text",
        "title" => "Translated string",
        "interface" => "json_editor",
        "schema" => $schemaForColumnTranslated,
        "show_column" => TRUE,
        "required" => TRUE,
      ],
    ]);
  }

  public function indexes($indexes = []) {
    return parent::indexes([
      "context___original" => [
        "type" => "index",
        "columns" => ["context", "original"],
      ],
    ]);
  }

}