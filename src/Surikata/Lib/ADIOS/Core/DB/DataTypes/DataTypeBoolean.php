<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

/**
 * Boolean data type.
 *
 * Converted to **boolean** in the SQL. Indexed by default. Default 0. NOT NULL.
 *
 * *UI/Input* renders *checkbox* for this data type.
 *
 * Example of definition in \ADIOS\Core\Model's column() method:
 * ```
 *   "myColumn" => [
 *     "type" => "boolean",
 *     "title" => "My Boolean Column",
 *     "show_column" => FALSE,
 *   ]
 * ```
 *
 * @package DataTypes
 */
class DataTypeBoolean extends DataType {
  public function get_sql_create_string($table_name, $col_name, $params = []) {
    $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' NOT NULL default 0 ';
    return "`{$col_name}` boolean {$params['sql_definitions']}";
  }

  public function get_sql_column_data_string($table, $colName, $value, $params = []) {
    $params = _put_default_params_values($params, [
      'null_value' => false,
      'dumping_data' => false,
      'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
    ]);

    return "`{$colName}` = ".((bool) $value ? 1 : 0);
  }

  /**
   * @internal
   */
  public function get_html($value, $params = []) {
    if ((int) $value !== 0) {
      $html = "<i class='fas fa-check-circle' style='color:#4caf50' title='".$this->translate("Yes")."'></i>";
    } else {
      $html = "<i class='fas fa-times-circle' style='color:#ff5722' title='".$this->translate("No")."'></i>";
    }

    return "<div style='text-align:center'>{$html}</div>";
  }

  public function get_csv($value, $params = []) {
    return (int) $value;
  }
}
