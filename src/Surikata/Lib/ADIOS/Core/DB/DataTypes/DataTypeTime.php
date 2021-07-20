<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeTime extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default null ';

        return "`$col_name` time {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
        ]);

        if (false == $params['null_value']) {
            $value = date('H:i:s', strtotime($value));
            $sql = "$col_name='$value'";
        } else {
            $sql = "$col_name=NULL";
        }

        return $sql;
    }

    public function get_html_or_csv($value, $params = [])
    {
        $html = '';

        if (isset($params['col_definition']['format'])) {
            $format = $params['col_definition']['format'];
        } else {
            $format = $this->adios->getConfig('m_datapub/columns/time/format', 'H:i:s');
        }

        $ts = strtotime($value);
        $html = (0 == $ts ? '' : date($format, $ts));

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
