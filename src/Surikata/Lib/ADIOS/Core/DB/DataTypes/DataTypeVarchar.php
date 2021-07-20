<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeVarchar extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : " default '' ";

        return "`$col_name` varchar({$params['byte_size']}) {$params['sql_definitions']}";
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

    public function get_html_or_csv($value, $params = [])
    {
        $html = '';

        $value = $params['export_csv'] ? $value : htmlspecialchars($value);

        if (is_array($params['col_definition']['enum_values'])) {
            $html = l($params['col_definition']['enum_values'][$value]);
        } else {
            $html = mb_substr($value, 0, ($params['col_definition']['wa_list_char_length'] ? $params['col_definition']['wa_list_char_length'] : 80), 'utf-8');
            if (strlen($html) < strlen($value)) {
                $html .= '...';
            }
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
