<?php

namespace ADIOS\Widgets\Products\Models;

class Supplier extends \ADIOS\Core\Model {
  var $sqlName = "suppliers";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Suppliers";
  var $tableTitle = "Suppliers";
  var $formTitleForInserting = "New supplier";
  var $formTitleForEditing = "Supplier";
  
  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => "Supplier name",
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' => "Description",
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => 'Logo',
        'show_column' => TRUE,
        "subdir" => "brands"
      ],
    ]);
  }

}