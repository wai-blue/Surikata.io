<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeText extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = []) {
        return "`$col_name` text ".($params['sql_definitions'] ?? "");
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        if ($params['null_value']) {
            $sql = "$col_name=NULL";
        } else {
            // $value = str_replace("'", "\\'", $value);
            $sql = "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
        }

        return $sql;
    }

    public function get_html($value, $params = [])
    {
        $value = 'yes' == $params['col_definition']['wa_list_no_html_convert'] ? $value : strip_tags($value);
        $html = mb_substr($value, 0, ($params['col_definition']['wa_list_char_length'] ? $params['col_definition']['wa_list_char_length'] : 80), 'utf-8');
        if (strlen($html) < strlen($value)) {
            $html .= '...';
        }
        $html = ($html);

        return $html;
    }

    public function get_csv($value, $params = [])
    {
        return $value;
    }
}
