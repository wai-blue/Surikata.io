<?php

namespace ADIOS\Plugins\WAI\News\Models;

class News extends \ADIOS\Core\Model {

  var $sqlName = "news";
  var $urlBase = "Website/News";
  var $lookupSqlValue = "{%TABLE%}.title";

  public function init() {
    $this->tableTitle = $this->translate("News");
  }

  public function columns($columns = []) {
    return parent::columns([
      "title" => [
        "type" => "varchar",
        "title" => $this->translate("Title"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "content" => [
        "type" => "text",
        "title" => $this->translate("Content"),
        "interface" => "formatted_text",
        "show_column" => TRUE,
      ],

      "image" => [
        "type" => "image",
        "title" => $this->translate("Image"),
        "required" => TRUE,
        "show_column" => TRUE,
        "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
      ],

      "perex" => [
        "type" => "text",
        "title" => "Perex",
        "interface" => "formatted_text",
        "show_column" => TRUE,
      ],

      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => FALSE,
        "show_column" => TRUE,
      ],

      "show_from" => [
        "type" => "date",
        "title" => $this->translate("Show from"),
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