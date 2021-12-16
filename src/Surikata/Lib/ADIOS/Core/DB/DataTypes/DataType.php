<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

/**
 * Basic class for definition of an ADIOS data type.
 *
 * @package DataTypes
 */
class DataType {
  public function __construct(&$adios) {
    $this->adios = $adios;
  }
  
  /**
   * Returns the SQL-formatted string used in CREATE TABLE queries.
   *
   * @param  string $table_name Deprecated and not used. Name of the table.
   * @param  string $col_name Name of the column to be created.
   * @param  array<string, mixed> $params Parameter of the column, e.g. default value.
   * @return string
   */
  public function get_sql_create_string($table_name, $col_name, $params = []) { }
  
  /**
   * Returns the SQL-formatted string used in INSERT or UPDATE queries.
   *
   * @param  string $table_name Deprecated and not used. Name of the table.
   * @param  string $col_name Name of the column to be updated.
   * @param  mixed $value Value to be inserted or updated.
   * @param  array<string, mixed> $params Parameter of the column.
   * @return void
   */
  public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) { }
  
  /**
   * Returns the HTML-formatted string of the given value.
   * Used in UI/Table element to format cells of the table.
   *
   * @param  mixed $value Value to be formatted.
   * @param  mixed $params Configuration of the HTML output (e.g. format of date string).
   * @return string HTML-formatted value.
   */
  public function get_html($value, $params = []) {
    return hsc($value);
  }

  /**
   * Returns the CSV-formatted string of the given value.
   * Used in UI/Table element for CSV exports.
   *
   * @param  mixed $value Value to be formatted.
   * @param  mixed $params Configuration of the HTML output (e.g. format of date string).
   * @return string CSV-formatted value.
   */
  public function get_csv($value, $params = []) {
    return $value;
  }

  public function translate($string) {
    return $this->adios->translate($string, $this);
  }
  
}

