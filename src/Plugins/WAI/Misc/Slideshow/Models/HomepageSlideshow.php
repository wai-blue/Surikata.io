<?php

namespace ADIOS\Plugins\WAI\Misc\Slideshow\Models;

class HomepageSlideshow extends \ADIOS\Core\Plugin\Model {
  var $sqlName = "homepage_slideshow";
  var $urlBase = "Website/Slideshow";

  public function init() {
    $this->tableTitle = $this->translate("Homepage slideshow");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => TRUE,
        "enum_values" => $this->adios->getEnumValuesForListOfDomains(),
        "show_column" => TRUE,
      ],

      "heading" => [
        "type" => "varchar",
        "title" => $this->translate("Heading"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "description" => [
        "type" => "varchar",
        "title" => $this->translate("Description"),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "button_url" => [
        "type" => "varchar",
        "title" => $this->translate("Button: URL (without root URL)"),
        "show_column" => TRUE,
      ],

      "button_text" => [
        "type" => "varchar",
        "title" => $this->translate("Button: Text"),
        "show_column" => TRUE,
      ],

      "image" => [
        "type" => "image",
        "title" => $this->translate("Image"),
        "required" => TRUE,
        "show_column" => TRUE,
        "description" => 
          $this->translate("Supported image extensions: jpg, gif, png, jpeg")
          ." ".$this->translate("Recommended resolution: 1200x500px.")
        ,
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