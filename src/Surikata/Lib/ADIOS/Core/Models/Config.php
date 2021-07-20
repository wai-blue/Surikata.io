<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Models;

class Config extends \ADIOS\Core\Model {
  var $sqlName = "";
  
  public function __construct($adios) {
    $this->sqlName = "{$adios->config['system_table_prefix']}_config";
    parent::__construct($adios);
  }

  public function columns(array $columns = []) {
    return [
      'path' => [
        'type' => 'varchar',
        'byte_size' => '250',
        'title' => 'Path',
        'show_column' => true
      ],
      'value' => [
        'type' => 'text',
        'interface' => 'plain_text',
        'title' => 'Value',
        'show_column' => true
      ],
    ];
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "path" => [
        "type" => "unique",
        "columns" => ["path"],
      ],
    ]);
  }

}