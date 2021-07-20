<?php

namespace ADIOS\Plugins\WAI\Misc\Slideshow\Models;

class UvodnaSlideshow extends \ADIOS\Core\Model {
  var $sqlName = "homepage_slideshow";
  var $tableTitle = "Homepage slideshow";
  var $urlBase = "Website/Slideshow";

  public function columns(array $columns = []) {
    return parent::columns([
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

      "url" => [
        "type" => "varchar",
        "title" => "URL",
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

}