<?php

namespace ADIOS\Widgets\Products\Models;

class Supplier extends \ADIOS\Core\Model {
  var $sqlName = "suppliers";
  var $lookupSqlValue = "concat({%TABLE%}.name)";
  var $urlBase = "Suppliers";

  public function init() {
    $this->tableTitle = $this->translate("Suppliers");
    $this->formTitleForInserting = $this->translate("New supplier");
    $this->formTitleForEditing = $this->translate("Supplier");
  }
  
  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        'type' => 'varchar',
        'title' => $this->translate("Supplier name"),
        'show_column' => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' => $this->translate("Description"),
        'show_column' => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => 'Logo',
        "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
        'show_column' => TRUE,
        "subdir" => "brands"
      ],
    ]);
  }

}