<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeYear extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default null ';

        return "`$col_name` year {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
        ]);

        if ($params['dumping_data']) {
            if (false == $params['null_value']) {
                if ('' == $value) {
                    $sql = "$col_name=NULL";
                } else {
                    $sql = "$col_name=$value";
                }
            }
        } else {
            if (false == $params['null_value']) {
                if ('' == $value) {
                    $sql = "$col_name=NULL";
                } else {
                    $sql = "$col_name=$value";
                }
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
        return $this->get_html_or_csv($value, $params);
    }
}
