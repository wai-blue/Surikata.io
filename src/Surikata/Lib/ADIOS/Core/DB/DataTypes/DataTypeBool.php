<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeBool extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : " default 'N' ";

        return "`$col_name` char(1) {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        if (1 === $value || '1' === $value || true === $value) {
            $value = 'Y';
        }
        if (0 === $value || '0' === $value || false === $value) {
            $value = 'N';
        }

        return "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
    }

    public function lipsum($table_name, $col_name, $col_definition, $params = [])
    {
        if (0 == rand(0, 2)) {
            return "'N'";
        } else {
            return "'Y'";
        }
    }

    public function get_heat_color($value)
    {
        $color = null;
        switch ($value) {
            case 'Y':
            case '1':
            case 'y':
                $color = '#63CE31';
                break;
            case 'N':
            case '0':
            case 'n':
                $color = '#FF6464';
                break;
        }

        return $color;
    }

    public function get_html_or_csv($value, $params = [])
    {
        if ('Y' == $value) {
            $html = l('áno');
        } else {
            $html = l('nie');
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
