<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

/**
 * @package DataTypes
 */
class DataTypeDate extends DataType {

  public function get_sql_create_string($table_name, $col_name, $params = []) {
    $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default null ';
    return "`{$col_name}` date {$params['sql_definitions']}";
  }

  public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) {
    $params = _put_default_params_values($params, [
      'null_value' => false,
      'dumping_data' => false,
    ]);

    if ($params['dumping_data']) {
      if (!$params['null_value']) {
        $sql = "`{$col_name}` = ".(empty($value) ? "NULL" : "'{$value}'");
      }
    } else {
      if (!$params['null_value']) {
        $date_value = str_replace(' ', '', $value);

        if (empty($date_value) || (0 == strtotime($date_value))) {
          $sql = "`{$col_name}` = NULL";
        } else {
          $date_value = date('Y-m-d', strtotime($value));

          if (!preg_match('/^\d\d\d\d-\d\d-\d\d$/', $date_value)) {
            $sql = "`{$col_name}` = NULL";
          } else {
            $sql = "`{$col_name}` = '{$date_value}'";
          }
        }
      }
    }

    return $sql;
  }

  public function get_html_or_csv($value, $params = []) {
    if (!empty($params['col_definition']['format'])) {
      $format = $params['col_definition']['format'];
    } else {
      $format = $this->adios->locale->dateFormat();
    }

    $ts = strtotime($value);
    $html = (0 == $ts ? '' : date($format, $ts));

    return $html;
  }

  public function get_html($value, $params = []) {
      return $this->get_html_or_csv($value, $params);
  }

  public function get_csv($value, $params = []) {
    return $this->get_html_or_csv($value, $params);
  }
}
