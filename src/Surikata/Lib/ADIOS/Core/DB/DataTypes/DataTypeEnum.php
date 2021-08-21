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
class DataTypeEnum extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $sql = "`$col_name` ENUM(";
        $e_vals = $params['enum_values'];
        $e_vals = str_replace('\,', '$%^@$%#$^%^$%#$^%$%@#$', $e_vals);
        $enum_values = explode(',', $e_vals);
        foreach ($enum_values as $key => $value) {
            $sql .= "'".trim(str_replace('$%^@$%#$^%^$%#$^%$%@#$', ',', $value))."', ";
        }
        $sql = substr($sql, 0, -2).") {$params['sql_definitions']}";

        return $sql;
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'use_string_values' => $this->adios->getConfig('m_datapub/columns/enum/use_string_values', true),
        ]);

        if ($params['dumping_data']) {
            if ('' == $value) {
                $sql = "$col_name=NULL";
            } else {
                $sql = "$col_name='$value'";
            }
        } else {
            if (!$params['null_value']) {
                $e_vals = explode(',', $this->adios->db->tables[$table_name][$col_name]['enum_values']);
                if (in_array($value, $e_vals)) {
                    $sql = "{$col_name}='".$this->adios->db->escape($value)."'";
                } else {
                    $sql = "$col_name=NULL";
                }
            } else {
                $sql = "$col_name=NULL";
            }
        }

        return $sql;
    }

    public function get_html_or_csv($value, $params = [])
    {
        $html = '';

        $value = strip_tags($value);
        $html = mb_substr($value, 0, ($params['col_definition']['wa_list_char_length'] ? $params['col_definition']['wa_list_char_length'] : 80), 'utf-8');
        if (strlen($html) < strlen($value)) {
            $html .= '...';
        }

        return $html;
    }

    public function get_html($value, $params = [])
    {
        return $this->get_html_or_csv($value, $params);
    }

    public function get_csv($value, $params = [])
    {
        return $value;
    }
}
