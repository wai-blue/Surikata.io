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
class DataTypeLookup extends DataType {

  public function get_sql_create_string($table_name, $col_name, $params = []) {
    $col_def = $this->adios->db->tables[$table_name][$col_name];

    if (!$col_def['disable_foreign_key']) {
      $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' NULL ';
    } else {
      $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default 0 ';
    }

    return "`{$col_name}` ".('' == $params['sql_type'] ? 'int(8)' : $params['sql_type'])." {$params['sql_definitions']}";
  }

  public function get_sql_column_data_string($table, $colName, $value, $params = []) {
    $colDefinition = $this->adios->db->tables[$table][$colName];

    $params = _put_default_params_values($params, [
      'null_value' => false,
      'dumping_data' => false,
      'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
    ]);

    if ($params['null_value']) {
      return "`{$colName}` = null";
    } else if (is_string($value) && !is_numeric($value)) {
      $model = $this->adios->getModel($colDefinition["model"]);
      $tmp = $model->getByLookupSqlValue($value);
      $id = (int) $tmp['id'];

      return "`{$colName}` = ".($id == 0 ? "null" : $id);
    } else {
      $value = (int) $value;

      if ($colDefinition['disable_foreign_key']) {
        $retval = "`{$colName}` = {$value}";
      } else {
        $retval = "`{$colName}` = ".($value == 0 ? "null" : $value);
      }

      return $retval;
    }
  }

  public function get_html_or_csv($value, $params = []) {
    $html = $params['row']["{$params['col_name']}_lookup_sql_value"] ?? "";
    return $params['export_csv'] ? $html : htmlspecialchars($html);
  }

  public function get_html($value, $params = []) {
    return $this->get_html_or_csv($value, $params);
  }

  public function get_csv($value, $params = []) {
    return $this->get_html_or_csv($value, $params);
  }
}
