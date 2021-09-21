<?php

namespace ADIOS\Widgets\Products\Models;

class Brand extends \ADIOS\Core\Model {
  var $sqlName = "brands";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Brands";
  var $tableTitle = "Brands";
  var $formTitleForInserting = "New brand";
  var $formTitleForEditing = "Brand";

  public static $allItemsCache = NULL;

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => "Brand or manufacturer name",
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' => "Short description",
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => "Logo",
        'show_column' => TRUE,
        "subdir" => "brand"
      ],
    ]);
  }

}