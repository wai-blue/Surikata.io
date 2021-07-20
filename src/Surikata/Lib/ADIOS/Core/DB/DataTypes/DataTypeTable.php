<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

class DataTypeTable extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        return '';
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        return '';
    }

    public function lipsum($table_name, $col_name, $col_definition, $params = [])
    {
        return '';
    }

    public function get_html_or_csv($value, $params = [])
    {
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
