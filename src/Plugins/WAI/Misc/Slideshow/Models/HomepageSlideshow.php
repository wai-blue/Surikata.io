<?php

namespace ADIOS\Plugins\WAI\Misc\Slideshow\Models;

class HomepageSlideshow extends \ADIOS\Core\Model {
  var $sqlName = "homepage_slideshow";
  var $tableTitle = "Homepage slideshow";
  var $urlBase = "Website/Slideshow";

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "heading" => [
        "type" => "varchar",
        "title" => "Heading",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "description" => [
        "type" => "varchar",
        "title" => "Description",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "button_url" => [
        "type" => "varchar",
        "title" => "Button: URL",
        "show_column" => TRUE,
      ],

      "button_text" => [
        "type" => "varchar",
        "title" => "Button: Text",
        "show_column" => TRUE,
      ],

      "image" => [
        "type" => "image",
        "title" => "Image",
        "required" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
    ]);
  }

}