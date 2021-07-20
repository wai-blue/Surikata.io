<?php

namespace ADIOS\Plugins\WAI\Delivery\DPD\Models;

class DPDVydajneMiesto extends \ADIOS\Core\Model {
  var $sqlName = "dpd_vydajne_miesta";
  var $urlBase = "DPD/VydajneMiesta";
  var $tableTitle = "DPD Výdajné miesta";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => "varchar",
        "title" => "Name",
        "show_column" => TRUE,
      ],

      "logo" => [
        'type' => 'image',
        'title' => 'Logo',
        'show_column' => TRUE,
      ],
    ]);
  }

}