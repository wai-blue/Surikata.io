<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeFloat extends DataType {

    public function get_sql_create_string($table_name, $col_name, $params = []) {
      $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default null ';
      $float_decimals = max((int) $params['decimals'], 1);
      $float_width = $float_decimals + 12;

      $sqlDataType = ($params['sql_data_type'] ?? "double");

      if (!in_array($sqlDataType, ["double", "float", "decimal", "numeric"])) {
        $sqlDataType = "double";
      }

      return "`{$col_name}` {$sqlDataType}($float_width, $float_decimals) {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        if ($params['dumping_data'] && '' == $value) {
            $value = '-1';
        }

        $value = str_replace(',', '.', $value);

        if ($params['null_value'] or '' == $value) {
            return "$col_name=NULL";
        } else {
            if (is_numeric($value) && '' != $value) {
                return "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
            } else {
                return "$col_name=null";
            }
        }
    }

    public function get_html($value, $params = [])
    {
        $html = '';

        if (isset($params['col_definition']['decimals'])) {
            $decimals = $params['col_definition']['decimals'];
        } else {
            $decimals = 2;
        }

        $value_number = number_format($value + 0, $decimals, ',', ' '); // str_replace(".", ",", strip_tags($value));

        if ('' == $params['col_definition']['format']) {
            $html = $value_number;
        } else {
            $html = str_replace('{%VALUE%}', $value_number, $params['col_definition']['format']);
        }

        if ($params['col_definition']['unit'] != "") $html .= " {$params['col_definition']['unit']}";

        return $html;
    }

    public function get_csv($value, $params = [])
    {
        return str_replace('.', ',', strip_tags($value + 0));
    }
}
