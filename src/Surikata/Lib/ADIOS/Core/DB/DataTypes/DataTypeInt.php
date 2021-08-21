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
class DataTypeInt extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default null ';

        return "`$col_name` int({$params['byte_size']}) {$params['sql_definitions']}";
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

        if ($params['null_value']) {
            $sql = "$col_name=NULL";
        } else {
            if (is_numeric($value) && '' != $value) {
                $sql = "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value + 0) : $value + 0)."'";
            } else {
                $sql = "$col_name=null";
            }
        }

        return $sql;
    }

    public function get_html_or_csv($value, $params = [])
    {
        $html = '';

        if (is_array($params['col_definition']['code_list'])) {
            if (is_numeric($value)) {
                $html = $params['col_definition']['code_list'][$value];
            } else {
                $html = $value;
            }
        } elseif (is_array($params['col_definition']['enum_values'])) {
            $html = l(
                $params['col_definition']['enum_values'][$value],
                [],
                ['input_column_settings_enum_translation' => true]
            );
        } else {
            $value_number = number_format((int) strip_tags($value) + 0, 0, '', ' ');

            if ('' == $params['col_definition']['format']) {
                $value = $value_number;
            } else {
                $value = str_replace('{%VALUE%}', $value_number, $params['col_definition']['format']);
            }

            if ($params['col_definition']['unit'] != "") $html .= " {$params['col_definition']['unit']}";

            $html = $value;
        }

        return $html;
    }

    public function get_html($value, $params = [])
    {
        return $this->get_html_or_csv($value, $params);
    }

    public function get_csv($value, $params = [])
    {
        return $this->get_html_or_csv($value, $params);
    }
}
