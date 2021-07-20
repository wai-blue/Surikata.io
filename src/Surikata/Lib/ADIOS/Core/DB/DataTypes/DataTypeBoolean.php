<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeBoolean extends DataType {
  public function get_sql_create_string($table_name, $col_name, $params = []) {
    $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' NOT NULL default 0 ';

    return "`$col_name` boolean {$params['sql_definitions']}";
  }

  public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) {
    $params = _put_default_params_values($params, [
      'null_value' => false,
      'dumping_data' => false,
      'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
    ]);

    if (0 != $value && '0' != $value && false != $value) {
      $value = 1;
    } else {
      $value = 0;
    }

    return "$col_name = {$value}";
  }

  public function get_html_or_csv($value, $params = []) {
    if ((int) $value !== 0) {
      $html = "<i class='fas fa-check-circle' style='color:#4caf50'></i>";
    } else {
      $html = "<i class='fas fa-times-circle' style='color:#ff5722'></i>";
    }

    return "<div style='text-align:center'>{$html}</div>";
  }

  public function get_html($value, $params = []) {
    return $this->get_html_or_csv($value, $params);
  }

  public function get_csv($value, $params = []) {
    return $this->get_html_or_csv($value, $params);
  }
}
