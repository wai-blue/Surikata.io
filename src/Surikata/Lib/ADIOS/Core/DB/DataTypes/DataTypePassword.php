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
class DataTypePassword extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : " default '' ";

        return "`$col_name` varchar({$params['byte_size']}) {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) {
      $params = _put_default_params_values($params, [
        'null_value' => false,
        'dumping_data' => false,
        'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
      ]);

      if ($params['null_value']) {
        $sql = "$col_name=NULL";
      } else {
        $pswd_1 = $params["data"]["{$col_name}_1"] ?? "";
        $pswd_2 = $params["data"]["{$col_name}_2"] ?? "";

        if ($pswd_1 != "" && $pswd_1 == $pswd_2) {
          $sql = "`{$col_name}` = '".password_hash($pswd_1, PASSWORD_DEFAULT)."'";
        }
      }

      return $sql;
    }

    public function get_html($value, $params = []) {
      return "...".substr(hsc($value), 8, 8)."...";
    }

    public function get_csv($value, $params = []) {
      return '';
    }
}
