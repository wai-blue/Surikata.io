<?php

namespace ADIOS\Plugins\WAI\News\Models;

class News extends \ADIOS\Core\Model {
  var $sqlName = "news";
  var $tableTitle = "News";
  var $urlBase = "Website/News";
  var $lookupSqlValue = "{%TABLE%}.title";

  public function columns($columns = []) {
    return parent::columns([
      "title" => [
        "type" => "varchar",
        "title" => "Title",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "content" => [
        "type" => "text",
        "title" => "Content",
        "interface" => "formatted_text",
        "show_column" => TRUE,
      ],

      "image" => [
        "type" => "image",
        "title" => "Image",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "perex" => [
        "type" => "text",
        "title" => "Perex",
        "interface" => "formatted_text",
        "show_column" => TRUE,
      ],

      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => FALSE,
        "show_column" => TRUE,
      ],

      "show_from" => [
        "type" => "date",
        "title" => "Show from",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function formParams($data, $params) {
    $params["default_values"] = [
      "show_from" => date("d.m.Y"),
    ];

    return $params;
  }

  // public function getById($id) {
  //   $id = (int) $id;
  //   $item = self::with('blogTags')->find($id);
  //   return ($item === NULL ? [] : $item->toArray());
  // }

}