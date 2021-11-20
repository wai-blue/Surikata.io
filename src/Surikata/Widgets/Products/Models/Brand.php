<?php

namespace ADIOS\Widgets\Products\Models;

class Brand extends \ADIOS\Core\Widget\Model {
  var $sqlName = "brands";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Brands";

  public static $allItemsCache = NULL;

  public function init() {
    $this->tableTitle = $this->translate("Brands");
    $this->formTitleForInserting = $this->translate("New brand");
    $this->formTitleForEditing = $this->translate("Brand");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Brand or manufacturer name"),
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' =>  $this->translate("Short description"),
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => "Logo",
        "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
        'show_column' => TRUE,
        "subdir" => "brand"
      ],
    ]);
  }

}