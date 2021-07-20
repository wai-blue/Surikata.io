<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataType {
  public function __construct(&$adios) {
    $this->adios = $adios;
  }

  public function get_sql_create_string($table_name, $col_name, $params = []) { }

  public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) { }

  public function lipsum($table_name, $col_name, $col_definition, $params = []) { }

  public function get_heat_color($value) { return null; }

  public function get_html($value, $params = []) { return ''; }
}

