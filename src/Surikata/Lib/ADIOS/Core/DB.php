<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

define('_SQL_COLUMN_WHERE_LIKE_BOTH', 'both');
define('_SQL_COLUMN_WHERE_LIKE_LEFT', 'left');
define('_SQL_COLUMN_WHERE_LIKE_RIGHT', 'right');

$onkeypress_request = '';

class DB
{
    /**
     * Multidimensional array which completely defines the table structure of a database
     * with which will datapub work.
     */
    public $tables;

    /**
     * m_login object which provides the information if any user is logged in.
     */
    public $login;

    /**
     * Constructor.
     *
     * @param string Name of this element
     * @param array Array of parameters for this module
     */
    public function __construct(&$adios, $params)
    {
        $this->adios = $adios;

        $this->db_host = $params['db_host'];
        $this->db_port = $params['db_port'];
        $this->db_login = $params['db_login'];
        $this->db_password = $params['db_password'];
        $this->db_name = $params['db_name'];
        $this->db_codepage = $params['db_codepage'];

        $this->connect();

        $this->ob_mode = false;
        $this->ob = '';
        $this->debug_correct_queries = false;

        $this->tables = [];

        $h = opendir(dirname(__FILE__).'/DB/DataTypes');
        while (false !== ($file = readdir($h))) {
            if ('.' != $file && '..' != $file) {
                $col_type = substr($file, 0, -4);
                $tmp = "m_datapub_column_{$col_type}";
                $this->register_column_type($col_type, $$tmp);
            }
        }
    }


    public function connect() {

      if (empty($this->db_host)) {
        throw new \ADIOS\Core\DBException("Database connection string is not configured.");
      }

      if (!empty($this->db_port) && is_numeric($this->db_port)) {
        $this->connection = new \mysqli($this->db_host, $this->db_login, $this->db_password, $this->db_name, $this->db_port);
      } else {
        $this->connection = new \mysqli($this->db_host, $this->db_login, $this->db_password);
      }

      if (!empty($this->connection->connect_error)) {
        throw new \ADIOS\Core\DBException($this->connection->connect_error);
      }

      $this->connection->select_db($this->db_name);

      if ($this->connection->errno == 1049) {
        // unknown database
        $this->query("
          create database if not exists `{$this->db_name}`
          default charset = utf8mb4
          default collate = utf8mb4_unicode_ci
        ");

        echo "Created database `{$this->db_name}`";

        $this->connection->select_db($this->db_name);

      }

      if (!empty($this->db_codepage)) {
        $this->connection->set_charset($this->db_codepage);
      }

    }

    function escape($str) {
      return $this->connection->real_escape_string((string) $str);
    }


    // /**
    //  * Selects a database to use. Sets the $db_error property, if an error occurs.
    //  *
    //  * @param string name of a database to use
    //  * @param string name of a code page of a database
    //  */
    // public function run_script($filename)
    // {
    //     $script = implode("\n", file($filename));
    //     $script = explode(';', $script);
    //     while (list($key, $value) = each($script)) {
    //         if ($value) {
    //             $this->query($value.';');
    //         }
    //     }
    // }

    public function ob_start()
    {
        $this->ob_mode = true;
        $this->ob = '';
    }

    public function ob_get_clean()
    {
        return $this->ob;
    }

    public function ob_finish()
    {
        $this->ob_mode = false;
    }

    /**
     * Runs a single SQL query. Result of a query is stored in a property $db_result.
     * Sets the $db_error property, if an error occurs.
     *
     * @param string SQL query to run
     *
     * @see multi_query
     * @see fetch_array
     */
    public function query($query, $initiatingModel = NULL) {
      $query = trim($query, " ;");
      if (empty($query)) return;

      $ts1 = _getmicrotime();
      $this->last_query = $query;
      $this->db_result = $this->connection->query($query);
      $this->last_query_duration = _getmicrotime() - $ts1;

      if (!empty($this->connection->error)) {
        $foreginKeyErrorCodes = [1062, 1216, 1217, 1451, 1452];
        $errorNo = $this->get_error_no();

        if (in_array($errorNo, $foreginKeyErrorCodes)) {
          $message = "Operation blocked by foreign key constraint.\n";
          if ($initiatingModel instanceof \ADIOS\Core\Model) {
            $message .= $initiatingModel->name;
          }

          throw new \ADIOS\Core\DBDuplicateEntryException("{$message} ERROR: {$this->connection->error} QUERY: {$query}");
        } else {
          throw new \ADIOS\Core\DBException($this->get_error().", QUERY: {$query}");
        }
      } else {
        if ($this->debug_correct_queries) {
          $this->adios->console->log('DB', "Query OK:\n{$query}");
        }
      }

      return $this->db_result;
    }

    /**
     * Uses query() method to run multiply SQL queries. Queries are separated
     * by a given separator, which is by default ";;\n".
     *
     * @param string multiple SQL query string to run
     *
     * @see query
     * @see fetch_array
     */
    public function multi_query($query, $separator = ";;\n", $disable_ob_mode = false, $force_blocked = false)
    {
        $query = str_replace("\r\n", "\n", $query);
        $script = explode($separator, $query);
        foreach ($script as $key => $value) {
            if ($value) {
                $this->query(trim($value).';', $disable_ob_mode, $force_blocked);
            }
        }
    }

    /**
     * Fetches one row from a database from a given $db_result. If $db_result is not
     * given or is NULL, then the internal $db_result property is used.
     * Sets the $db_error property, if an error occurs.
     *
     * @param string DB result identifier to fetch row from
     *
     * @see query
     * @see multi_query
     * @see num_rows
     */
    public function fetch_array($result = null) {
      if (!$result) {
        $result = $this->db_result;
      }
      $row = $result->fetch_array(MYSQLI_ASSOC);

      return $row;
    }

    /**
     * Fetches one row from a database from a given $db_result. If $db_result is not
     * given or is NULL, then the internal $db_result property is used.
     * Sets the $db_error property, if an error occurs.
     *
     * @param string DB result identifier to fetch row from
     *
     * @see query
     * @see multi_query
     * @see num_rows
     */
    public function fetch_assoc($result = null) {
      if (!$result) {
        $result = $this->db_result;
      }
      $row = $result->fetch_assoc(MYSQLI_ASSOC);

      return $row;
    }

    /**
     * Counts number of items from a given $db_result. If $db_result is not
     * given or is NULL, then the internal $db_result property is used.
     *
     * @param string DB result identifier to count items in
     *
     * @see query
     * @see multi_query
     * @see fetch_array
     */
    public function num_rows($result = null) {
      if (!$result) {
        $result = $this->db_result;
      }

      return mysqli_num_rows($result);
    }

    public function affected_rows($result = null) {
      if (!$result) {
        $result = $this->db_result;
      }

      return mysqli_affected_rows($result);
    }

    public function insert_id() {
      return $this->connection->insert_id;
    }

    public function check_query($query) {
      $regex = '('; // begin group
      $regex .= '(?:--|\\#)[\\ \\t\\S]*'; // inline comments
      $regex .= '|(?:<>|<=>|>=|<=|==|=|!=|!|<<|>>|<|>|\\|\\||\\||&&|&|-|\\+|\\*(?!\/)|\/(?!\\*)|\\%|~|\\^|\\?)'; // logical operators
      $regex .= '|[\\[\\]\\(\\),;`]|\\\'\\\'(?!\\\')|\\"\\"(?!\\"")'; // empty single/double quotes
      $regex .= '|".*?(?:(?:""){1,}"|(?<!["\\\\])"(?!")|\\\\"{2})|\'.*?(?:(?:\'\'){1,}\'|(?<![\'\\\\])\'(?!\')|\\\\\'{2})'; // quoted strings
      $regex .= '|\/\\*[\\ \\t\\n\\S]*?\\*\/'; // c style comments
      $regex .= '|(?:[\\w:@]+(?:\\.(?:\\w+|\\*)?)*)'; // words, placeholders, database.table.column strings
      $regex .= '|[\t\ ]+';
      $regex .= '|[\.]'; //period
      $regex .= '|[\s]'; //whitespace
      $regex .= ')'; // end group

      // get global match
      preg_match_all('/'.$regex.'/smx', $query, $result);

      $tokens = [];
      foreach ($result[0] as $key => $value) {
        if ('' !== trim($value)) {
          $tokens[] = $value;
        }
      }

      return ['query' => $query, 'tokens' => $tokens];
    }

    public function get_error() {
      return $this->connection->error;
    }

    public function get_error_no() {
      return $this->connection->errno;
    }






















    /**
     * Returns html string with initialization. This string should be placed in the <head> tag of the webpage.
     */
    public function get_init()
    {
        return "";
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    // functions for manipulating with table definitions in the database
    /////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Returns array of essential columns which must be included in each "datapub" table
     * Appends also the $add_columns to the result.
     *
     * @param array array of additional columns
     *
     * @see parse_tables
     */
    public function basic_table_columns($add_columns = [])
    {
        $basic_columns = $this->adios->getConfig('m_datapub/basic_table_columns',
            [
                'id' => [
                    'type' => 'int',
                    'byte_size' => '8',
                    'sql_definitions' => 'primary key auto_increment',
                    'title' => 'ID',
                    'only_display' => 'yes',
                    'wa_list_display' => 'yes',
                    // 'show_column' => true,
                    'column_width' => '60px;',
                    'white_space' => 'nowrap',
                    // 'style' => 'width:80px;color:var(--cl-main);',
                    'class' => 'primary-key'
                ],
            ]
        );

        // pridanie automatickych indexov do struktury indexov
        if (_count($add_columns)) {
            foreach ($add_columns as $col_name => $col_definition) {
                if (in_array($col_definition['type'], ['lookup', 'int', 'bool', 'boolean', 'date'])) {
                    $add_columns['%%table_params%%']['indexes']['index'][$col_name] = ['columns' => [$col_name], 'adios_autoindex' => 1];
                }
            }
        }

        // kontrola definicie indexov
        $used_index_names = [];
        if (_count($add_columns['%%table_params%%']['indexes']) > 0) {
            foreach ($add_columns['%%table_params%%']['indexes'] as $index_type => $indexes) {
                if (_count($indexes)) {
                    foreach ($indexes as $index_name => $index_data) {
                        if (_count($index_data['columns'])) {
                            // existuje column v tables?
                            foreach ($index_data['columns'] as $index_for_column) {
                                if (!array_key_exists($index_for_column, $add_columns)) {
                                    _d_echo("DB", "", "WRONG COLUMN: '{$index_for_column}' FOR INDEX NAME {$index_name} IN TABLE ".l($add_columns['%%table_params%%']['title']));
                                }
                            }
                        } else {
                            // nie su definovane columns
                            _d_echo("DB", "", "MISSING COLUMNS FOR INDEX NAME {$index_name} IN TABLE ".l($add_columns['%%table_params%%']['title']));
                        }
                        // duplicitny nazov indexu
                        if ($used_index_names[$index_name]) {
                            _d_echo("DB", "", "DUPLICATE INDEX NAME {$index_name} IN TABLE ".l($add_columns['%%table_params%%']['title']));
                        } else {
                            $used_index_names[$index_name] = 1;
                        }
                    }
                }
            }
        }

        $forbidden_columns = array_keys($basic_columns);
        if (_count($add_columns)) {
            foreach ($add_columns as $key => $value) {
                if (in_array($key, $forbidden_columns)) {
                    _print_r($add_columns);
                    _d_echo("DB", "", "Column $key is a forbidden column!");
                }
            }

            return array_merge($basic_columns, $add_columns);
        } else {
            return $basic_columns;
        }
    }

    public function is_basic_table_column($col_name)
    {
        return '%%table_params%%' == $col_name || 'id' == $col_name;
    }

    public function register_column_type($column_type, $column_object)
    {
        $class = "\\ADIOS\\Core\\DB\\DataTypes\\{$column_type}";

        $tmp = str_replace("DataType", "", $column_type);
        $tmp = strtolower($tmp);
        $this->registered_columns[$tmp] = new $class($this->adios);
    }

    public function is_registered_column_type($column_type)
    {
        return isset($this->registered_columns[$column_type]);
    }

    public function _is_registered_column_type($column_type)
    {
        return $this->is_registered_column_type($column_type);
    }

    public function add_created_modified_columns($tables)
    {
        global $gtp;
        foreach ($tables as $table_name => $table_columns) {
        }

        return $tables;
    }

    /**
     * Returns the array of column parameters out of its string representation.
     *
     * @param array array of column parameters
     */
    public function _str2col($params)
    {
        $return = [];
        $param_arr = explode('|', $params);
        foreach ($param_arr as $key => $value) {
            $value = trim($value);
            $params = explode(' ', $value);
            $param_name = $params[0];
            array_shift($params);
            $param_value = join(' ', $params);

            $return[$param_name] = $param_value;
        }

        return $return;
    }

    /**
     * Loads table definitions from a filename.
     *
     * @param string filename to load from
     */
    public function load_tables($filename)
    {
        global $gtp;
        if (file_exists($filename)) {
            include $filename;
            $this->tables = array_merge($this->tables, $_tables);
            unset($_tables);
        }
    }

    public function load_tables_from_dir($dir) {
        global $gtp;

        if (is_dir($dir)) {
            $_tables = [];
            foreach (scandir($dir) as $file) {
                if (is_file("{$dir}/{$file}")) {
                    $_table = NULL;
                    include("{$dir}/{$file}");
                    if (is_array($_table)) {
                        $this->tables["{$gtp}_".str_replace(".php", "", $file)] = $this->basic_table_columns($_table);
                    }
                }
            }
        }
    }

    public function addTable($table_name, $table_definition) {
      $this->tables[$table_name] = $this->basic_table_columns($table_definition);
    }

    /**
     * Returns TRUE if table with given name is defined in datapub tables.
     *
     * @param string name of a table to identify
     */
    public function table_exists($table_name)
    {
        return isset($this->tables[$table_name]);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    // functions for manipulating data in the database
    /////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Returns SQL command which will create empty SQL table according to table
     * definition.
     *
     * @param string name of a table to create
     */
    public function _sql_table_create($table_name) {
      if (!isset($this->tables[$table_name])) {
        exit("_sql_table_create: table $table_name was not found in INI file!");
      }

      $table_columns = $this->tables[$table_name];
      $table_params = $table_columns['%%table_params%%'];

      $sql = "drop table if exists `{$table_name}`;;\n";
      $sql .= "create table `{$table_name}` (\n";

      foreach ($table_columns as $key => $value) {
        // $key = nazov stlpca, $value = parametre
        $col_name = trim($key);
        $col_type = trim($value['type']);

        if (isset($this->registered_columns[$col_type]) && !$value['virtual']) {
          $tmp = $this->registered_columns[$col_type]
            ->get_sql_create_string($table_name, $col_name, $value)
          ;
          if (!empty($tmp)) {
            $sql .= "  {$tmp},\n";
          }
        }
      }

      // indexy
      if (_count($table_params['indexes']) > 0) {
        foreach ($table_params['indexes'] as $index_type => $indexes) {
          if (_count($indexes)) {
            if (in_array($index_type, ['index', 'unique'])) {
              if ($index_type == 'index') {
                $index_sql_def = 'index';
              } else if ($index_type == 'unique') {
                $index_sql_def = 'unique index';
              }

              foreach ($indexes as $index_name => $index_data) {
                $sql .= "  {$index_sql_def} {$index_name} (".implode(',', $index_data['columns'])."),\n";
              }
            }
          }
        }
      }

      $sql = substr($sql, 0, -2).")";

      $sql .= " ENGINE = ".($table_params['engine'] ?? "InnoDB").";;\n";

      return $sql;
    }

    /**
     * Creates given table in the SQL database. In other words: executes
     * SQL command returned by _sql_table_create.
     *
     * @param string name of a table
     * @param bool If this param is TRUE, it only returns the SQL command to be executed
     *
     * @see _sql_table_create
     */
    public function create_sql_table($table_name, $only_sql_command = false, $force_create = false)
    {
        $do_create = true;

        $log_status = $this->log_disabled;
        $this->log_disabled = 1;
        if (!$force_create) {
            try {
                $cnt = $this->count_all_rows_query("select * from `{$table_name}`");
            } catch (\ADIOS\Core\DBException $e) {
                $cnt = 0;
            }

            if ($cnt > 0) {
                $do_create = false;
            }
        }

        if ($do_create) {

            $sql = $this->_sql_table_create($table_name);

            if ($only_sql_command) {
                return $sql;
            } else {
                $this->multi_query($sql);
            }

        }
        $this->log_disabled = $log_status;
    }

    public function recreate_sql_table($table_name) {
      $this->query("SET foreign_key_checks = 0");
      $this->query("drop table if exists `{$table_name}`");
      $this->create_sql_table($table_name, FALSE, TRUE);
      $this->query("SET foreign_key_checks = 1");
    }

    public function create_foreign_keys($table_name) {
        $table_columns = $this->tables[$table_name];

        $sql = '';
        foreach ($table_columns as $col_name => $col_definition) {
            if (!$col_definition['disable_foreign_key'] && 'lookup' == $col_definition['type']) {
                $lookupModel = $this->adios->getModel($col_definition['model']);

                $sql .= "
                    ALTER TABLE `{$table_name}`
                    ADD CONSTRAINT `fk_".md5($table_name.'_'.$col_name)."`
                    FOREIGN KEY (`{$col_name}`)
                    REFERENCES `".$lookupModel->getFullTableSQLName()."` (`id`);;
                ";
            }
        }

        if(!empty($sql)) {
            $this->multi_query($sql);
        }
    }

    public function get_pk($table_name)
    {
        $pk = $this->tables[$table_name]['%%table_params%%']['primary_key'];

        return '' == $pk ? 'id' : $pk;
    }

    /**
     * Returns SQL command which - when executed - fills the SQL table with the data
     * now stored in the database.
     *
     * @param string name of a table to dump
     * @param bool if this param is TRUE, it also creates the table
     */
    public function dump_data($table_name, $table_create = false)
    {
        $sql = '';

        if ($table_create) {
            $sql .= $this->_sql_table_create($table_name)."\n\n";
        }

        $pk = $this->get_pk($table_name);
        $rows = $this->get_all_rows($table_name, ["order" => "{$pk} asc"]);
        if (is_array($rows)) {
            foreach ($rows as $key => $value) {
                $sql .= $this->insert_row_query($table_name, $value, $dumping_data = true).";\n";
            }
        }

        return $sql;
    }

    /**
     * Returns part of SQL command representing the value of specified column to
     * be inserted or updated. Used in insert_row, update_row and update_row_part
     * methods.
     *
     * @param string name of a table
     * @param string  name of a column
     * @param array Array of values. One of the keys HAS TO BE the name of the column!
     * @param bool if this param is TRUE, the returned string is generated exclusively for the dump_data() method
     *
     * @see insert_row
     * @see update_row
     * @see update_row_part
     */
    public function _sql_column_data($table_name, $col_name, $data, $dumping_data = false) {
      $col_type = $this->tables[$table_name][$col_name]['type'];

      $value = $data[$col_name];
      $value_exists = array_key_exists($col_name, $data);

      $sql = '';

      // ak je hodnota stlpca definovana ako pole, tak moze mat rozne parametre
      if (is_array($value)) {
        if (isset($value['sql']) && '' != trim($value['sql'])) {
          $sql = "`{$col_name}` = ({$value['sql']})";
        }
      } else {
        if (isset($this->registered_columns[$col_type])) {
          $sql = $this->registered_columns[$col_type]->get_sql_column_data_string(
            $table_name,
            $col_name,
            $data[$col_name],
            [
              'null_value' => !$value_exists,
              'dumping_data' => $dumping_data,
              'data' => $data,
            ]
          );
        }
      }

      return (empty($sql) ? "" : "{$sql}, ");
    }

//
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Inserts into change_tracker_table a row representing atomic operation on a database.
     *
     * @param string name of a table
     * @param int ID of a row which was manipulated
     * @param string Type of manipulation action: update, insert, delete...
     * @param int ID of an user who executed the action
     *
     * @see insert_row
     * @see update_row
     * @see update_row_part
     */

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // insert_row_query, insert_row

    /**
     * Returns SQL string representing command which would insert a row into a database.
     *
     * @param string name of a table
     * @param array array of data to be inserted
     * @param bool if this param is TRUE, the returned string is generated exclusively for the dump_data() method
     *
     * @see _sql_column_data
     */
    public function insert_row_query($table_name, $data, $dumping_data = false)
    {
        if ($dumping_data) {
            $SQL = "insert into $table_name set ";
        } else {
            $SQL = "insert into $table_name set ";

            if (!isset($data['id']) || $data['id'] <= 0) {
                $SQL .= "`id`=null, ";
            } else {
                $SQL .= "`id`='".$this->escape($data['id'])."', ";
                unset($data['id']);
            }

        }

        foreach ($this->tables[$table_name] as $col_name => $col_definition) {
            if (!$col_definition['virtual'] && $col_name != '%%table_params%%') {
                if ($data[$col_name] !== NULL) {
                    if (strpos((string) $data[$col_name], "SQL:") === 0) {
                        $tmp_sql = "`{$col_name}` = (".substr($data[$col_name], 4)."), ";
                    } else {
                        $tmp_sql = $this->_sql_column_data($table_name, $col_name, $data, $dumping_data);
                    }

                    $SQL .= $tmp_sql;

                } else if (!empty($col_definition['default_value'])) {
                    $SQL .= $col_definition['default_value'];
                }
            }
        }
        $SQL = substr($SQL, 0, -2).';;';

        return $SQL;
    }

    /**
     * Executes SQL command generated by a insert_row_query() method.
     *
     * @param string name of a table
     * @param array array of data to be inserted
     * @param bool if this param is TRUE, the SQL command is not executed, only returned as a string
     * @param bool if this param is TRUE, the returned string is generated exclusively for the dump_data() method
     *
     * @see insert_row_query
     */
    public function insert_row($table_name, $data, $only_sql_command = false, $dumping_data = false) {
      global $_FILES;

      $allowed = true;

      if ($data['id'] <= 0) {
        unset($data['id']);
      }

      $sql = $this->insert_row_query($table_name, $data, $dumping_data);

      if ($only_sql_command) {
        return $sql."\n";
      } else {
        $this->multi_query($sql);
        $inserted_id = $this->insert_id();

        return $inserted_id;
      }
    }

    public function insert_random_row($table_name, $data = [], $dictionary = []) {
      if (is_array($this->tables[$table_name])) {
        foreach ($this->tables[$table_name] as $col_name => $col_definition) {
          if ($col_name != "id" && !isset($data[$col_name])) {
            $random_val = NULL;
            if (is_array($dictionary[$col_name])) {
              $random_val = $dictionary[$col_name][rand(0, count($dictionary[$col_name]) - 1)];
            } else {
              switch ($col_definition['type']) {
                case "int":
                  if (is_array($col_definition['enum_values'])) {
                    $keys = array_keys($col_definition['enum_values']);
                    $random_val = $keys[rand(0, count($keys) - 1)];
                  } else {
                    $random_val = rand(0, 1000);
                  }
                break;
                case "float": $random_val = rand(0, 1000) / ($col_definition['decimals'] ?? 2); break;
                case "time": $random_val = rand(10, 20).":".rand(10, 59); break;
                case "date": $random_val = date("Y-m-d", time() - (3600*24*365) + rand(0, 3600*24*365)); break;
                case "datetime": $random_val = date("Y-m-d H:i:s", time() - (3600*24*365) + rand(0, 3600*24*365)); break;
                case "boolean": $random_val = (rand(0, 1) ? 1 : 0); break;
                case "text":
                  switch (rand(0, 5)) {
                    case 0:
                      $random_val = "Nunc ac sollicitudin ipsum. Vestibulum condimentum vitae justo quis bibendum. Fusce et scelerisque dui, eu placerat nisl. Proin ut efficitur velit, nec rutrum massa.";
                    break;
                    case 1:
                      $random_val = "Integer ullamcorper lacus at nisi posuere posuere. Maecenas malesuada magna id fringilla sagittis. Nam sed turpis feugiat, placerat nisi et, gravida lacus. Curabitur porta elementum suscipit.";
                    break;
                    case 2:
                      $random_val = "Praesent libero diam, vulputate sed varius eget, luctus a risus. Praesent sit amet neque commodo, varius nisl dignissim, tincidunt magna. Nunc tincidunt dignissim ligula, sit amet facilisis felis mollis vel.";
                    break;
                    case 3:
                      $random_val = "Sed ut ligula luctus, ullamcorper felis nec, tristique lorem. Maecenas sit amet tincidunt enim.";
                    break;
                    case 4:
                      $random_val = "Mauris blandit ligula massa, sit amet auctor risus viverra at. Cras rhoncus molestie malesuada. Sed facilisis blandit augue, eu suscipit lectus vehicula quis. Mauris efficitur elementum feugiat.";
                    break;
                    default:
                      $random_val = "Nulla posuere dui sit amet elit efficitur iaculis. Cras elit ligula, feugiat vitae maximus quis, volutpat sit amet sapien. Vivamus varius magna fermentum dolor varius, vel scelerisque ante mollis.";
                    break;
                  }
                case "varchar":
                case "password":
                  if (empty($col_definition['pattern'])) {
                    switch (rand(0, 5)) {
                      case 0: $random_val = rand(0, 9)." Nunc"; break;
                      case 1: $random_val = rand(0, 9)." Efficitur"; break;
                      case 2: $random_val = rand(0, 9)." Vulputate"; break;
                      case 3: $random_val = rand(0, 9)." Ligula luctus"; break;
                      case 4: $random_val = rand(0, 9)." Mauris"; break;
                      case 5: $random_val = rand(0, 9)." Massa"; break;
                      case 6: $random_val = rand(0, 9)." Auctor"; break;
                      case 7: $random_val = rand(0, 9)." Molestie"; break;
                      case 8: $random_val = rand(0, 9)." Malesuada"; break;
                      case 9: $random_val = rand(0, 9)." Facilisis"; break;
                      case 10: $random_val = rand(0, 9)." Augue"; break;
                    }
                  } else {
                    // 1. Read the grammar.
                    $grammar  = new \Hoa\File\Read('hoa://Library/Regex/Grammar.pp');

                    // 2. Load the compiler.
                    $compiler = \Hoa\Compiler\Llk\Llk::load($grammar);

                    // 3. generate random string
                    $generator = new \Hoa\Regex\Visitor\Isotropic(new \Hoa\Math\Sampler\Random());
                    $random_val = $generator->visit($compiler->parse($col_definition['pattern']));
                  }
                break;
              }
            }
            
            if ($random_val !== NULL) {
              $data[$col_name] = $random_val;
            }
          }
        }
      }
      
      return $this->insert_row($table_name, $data);
    }

//
    ////////////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // update_row_query, update_row

    /**
     * Returns SQL string representing command which would update a row into a database.
     *
     * @param string name of a table
     * @param array array of data to be inserted
     * @param int ID of a row to be inserted
     * @param bool if this param is TRUE, the values not present in $data array are left untouched
     *
     * @see _sql_column_data
     */
    public function update_row_query($table_name, $data, $id, $whole_row)
    {
        global $_FILES;

        $pk = $this->get_pk($table_name);

        // $data = array_merge($data, $_FILES);
        if (is_array($_FILES)) {
            foreach ($_FILES as $key => $value) {
                if (null !== $data[$key]) {
                    $data[$key] = $value;
                }
            }
        }

        //$data[$pk] = $id;

        $SQL = "update $table_name set ";
        foreach ($this->tables[$table_name] as $col_name => $col_definition) {
            if (!$col_definition['virtual'] && '%%table_params%%' != $col_name /* && $col_name != "owner" */ && 'rights' != $col_name) {
                // when user wants to delete a value by entering an empty string...
                if ($whole_row) {
                    if (
                      null == $data[$col_name]
                      && (
                        'varchar' == $col_definition['type']
                        || 'text' == $col_definition['type']
                        || 'password' == $col_definition['type']
                        || 'float' == $col_definition['type']
                        || 'int' == $col_definition['type']
                      )
                    ) {
                        $data[$col_name] = '';
                    }
                }

                if (array_key_exists($col_name, $data) && 'yes' != $col_definition['no_update']) {
                    $SQL .= $this->_sql_column_data($table_name, $col_name, $data);
                }
            }
        }
        $SQL = substr($SQL, 0, -2)." where $pk=$id;";

        return $SQL;
    }

    /**
     * Executes SQL command generated by a update_row_query() method. Values which are not present in $data
     * parameter are treated as empty values (empty string or zero).
     *
     * @param string name of a table
     * @param array array of data to be updated
     * @param int ID of a row to be updated
     * @param bool if this param is TRUE, the SQL command is not executed, only returned as a string
     *
     * @see update_row_query
     * @see update_row_part
     */
    public function update_row($table_name, $data, $id, $only_sql_command = false)
    {
        $allowed = true;
        $my_data = $data;

        if (_count($this->tables[$table_name])) {
            foreach ($this->tables[$table_name] as $col_name => $col_definition) {
                if (!$col_definition['virtual'] && '%%table_params%%' != $col_name && 'rights' != $col_name) {
                    $my_data_perms_callback[$col_name] = '';
                }
            }
        }

        $sql = $this->update_row_query($table_name, $my_data, $id, $whole_row = true);
        _d_echo("DB", "", "update_row: $sql");
        if ($only_sql_command) {
            return $sql;
        } else {

            $this->query($sql);

            return true;
        }
    }

    /**
     * Executes SQL command generated by a update_row_query() method. Similar to update_row() method, with one
     * difference: values which are not present in $data parameter are left unchanged.
     *
     * @param string name of a table
     * @param array array of data to be inserted
     * @param int ID of a row to be updated
     * @param bool if this param is TRUE, the SQL command is not executed, only returned as a string
     *
     * @see update_row_query
     * @see update_row
     */
    public function update_row_part($table_name, $data, $id, $only_sql_command = false)
    {
        $allowed = true;
        $my_data = $data;

        $sql = $this->update_row_query($table_name, $my_data, $id, $whole_row = false);

        if ($only_sql_command) {
            return $sql;
        } else {
            $this->query($sql);
            $error = $this->get_error();

            if ('' != $error) {
                $this->db_rights_callback_return['error'] = $error;

                return false;
            }

            return true;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // delete_row_where, delete_row

    /**
     * Deletes rows which match the given criteria. For "image" or "file" columns deletes the
     * relevant image (file).
     *
     * @param string name of a table
     * @param string SQL condition determining the rows to be deleted
     */
    public function delete_row_where($table_name, $where = '')
    {
        if ('' != $where) {
            $where = "where $where";
        }
        $pk = $this->get_pk($table_name);
        _d();

        $log_status = $this->adios->config['log_db']['enabled'];
        $this->adios->config['log_db']['enabled'] = 0;

        $tmp = $this->get_all_rows_query("select {$pk} from {$table_name} {$where}");
        foreach ($tmp as $value) {
            $this->delete_row($table_name, $value[$pk]);
        }

        $this->adios->config['log_db']['enabled'] = $log_status;

    }

    /**
     * Deletes row with given ID. For "image" or "file" columns deletes the relevant image (file).
     *
     * @param string name of a table
     * @param int ID of a row to delete
     */
    public function delete_row($table_name, $id) {
      $ret = $this->query("delete from `{$table_name}` where id = ".(int) $id." limit 1");

      return $ret;
    }

//
    ////////////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // copy

    /**
     * Copies the row with given ID to a new row and returns ID of an inserted item.
     *
     * @param string name of a table
     * @param int ID of a row to copy
     */
    public function copy($table_name, $id)
    {
        $pk = $this->get_pk($table_name);
        $data = $this->get_row($table_name, "$pk=$id");

        foreach (array_keys($this->basic_table_columns()) as $col_name) {
            unset($data[$col_name]);
        }
        $inserted_id = $this->insert_row($table_name, $data);

        if (isset($this->tables[$table_name]['placement'])) {
            $this->query("update {$table_name} set placement=".($inserted_id + 0).' where id='.($inserted_id + 0));
        }

        foreach ($this->tables[$table_name] as $col_name => $col_definition) {
            if ('' != $data[$col_name]) {
                $pom = explode('.', $data[$col_name]);
                $ext = $pom[count($pom) - 1];
                if ('image' == $col_definition['type']) {
                    @copy("{$this->adios->config['files_dir']}/{$data[$col_name]}", "{$this->adios->config['files_dir']}/{$table_name}_{$col_name}_{$inserted_id}.{$ext}");
                    $this->query("update $table_name set $col_name='{$table_name}_{$col_name}_{$inserted_id}.{$ext}' where $pk=$inserted_id");
                }
                if ('file' == $col_definition['type']) {
                    @copy("{$this->adios->config['files_dir']}/{$data[$col_name]}", "{$this->adios->config['files_dir']}/{$table_name}_{$col_name}_{$data[$pk]}.{$ext}");
                    $this->query("update $table_name set $col_name='{$table_name}_{$col_name}_{$inserted_id}.{$ext}' where $pk=$inserted_id");
                }
            }
        }

        return $inserted_id;
    }

//
    ////////////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // move_up, move_down

    /**
     * Moves a row with given ID one row up and returns its ID when succesful.
     * Returns -1 if operation was unsuccessful (row was already at the top).
     *
     * @param string name of a table
     * @param int ID of a row to move
     */
    public function move_up($table, $id)
    {
        if (!isset($this->tables[$table]['placement'])) {
            return;
        }

        $pk = $this->get_pk($table);

        $all_ids = [];
        $current_placement = 0;
        $next_placement = 0;
        $tmp_prev_placement = 0;
        $tmp = $this->get_all_rows_query("select $pk,placement from {$table} order by placement");
        foreach ($tmp as $key => $value) {
            $all_ids[] = $value['id'];
            if ($value['id'] == $id) {
                $current_placement = $value['placement'];
                $prev_placement = $tmp_prev_placement;
            }

            $tmp_prev_placement = $value['placement'];
        }

        if (0 != $current_placement && 0 != $prev_placement) {
            $this->query('start transaction');
            $this->query("set @max_placement = (select max(placement) from {$table})");
            $this->query("update {$table} set placement=(@max_placement+1) where placement=$current_placement");
            $this->query("update {$table} set placement=$current_placement where placement=$prev_placement");
            $this->query("update {$table} set placement=$prev_placement where placement=(@max_placement+1)");
            $this->query('commit');
        }
    }

    /**
     * Moves a row with given ID one row down and returns its ID when succesful.
     * Returns -1 if operation was unsuccessful (row was already on the bottom).
     *
     * @param string name of a table
     * @param int ID of a row to move
     */
    public function move_down($table, $id)
    {
        if (!isset($this->tables[$table]['placement'])) {
            return;
        }

        $pk = $this->get_pk($table);

        $all_ids = [];
        $current_placement = 0;
        $next_placement = 0;
        $tmp = $this->get_all_rows_query("select $pk,placement from {$table} order by placement");
        $get_next_placement = false;
        foreach ($tmp as $key => $value) {
            if ($get_next_placement) {
                $next_placement = $value['placement'];
            }
            $get_next_placement = false;
            $all_ids[] = $value['id'];
            if ($value['id'] == $id) {
                $current_placement = $value['placement'];
                $get_next_placement = true;
            }
        }

        if (0 != $current_placement && 0 != $next_placement) {
            $this->query('start transaction');
            $this->query("set @max_placement = (select max(placement) from {$table})");
            $this->query("update {$table} set placement=(@max_placement+1) where placement=$current_placement");
            $this->query("update {$table} set placement=$current_placement where placement=$next_placement");
            $this->query("update {$table} set placement=$next_placement where placement=(@max_placement+1)");
            $this->query('commit');
        }
    }

    //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////////////////////
    // functions for retrieving data from database
    /////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function filter($col_name, $col_type, $value, $params = [])
    {
        if (false !== strpos('.', $col_name)) {
            list($table_name, $col_name) = explode('.', $col_name);
        }
        if (is_object($this->registered_columns[$col_type])) {
            return ('' == $table_name ? '' : "{$table_name}.").$this->registered_columns[$col_type]->filter($col_name, $value, $params);
        } else {
            return 'TRUE';
        }
    }

    public function aggregate($input_column, $output_column, $aggregate_function)
    {
        if ('count' == $aggregate_function) {
            return "$aggregate_function($input_column) as $output_column";
        } elseif ('min' == $aggregate_function || 'max' == $aggregate_function || 'sum' == $aggregate_function || 'avg' == $aggregate_function || 'count' == $aggregate_function) {
            return "$aggregate_function(ifnull($input_column, 0)) as $output_column";
        } elseif ('group_concat' == $aggregate_function) {
            return "group_concat($input_column separator ', ') as $output_column";
        } elseif ('count_distinct' == $aggregate_function) {
            return "count(distinct $input_column) as $output_column";
        } elseif ('null' == $aggregate_function) {
            return "null as $output_column";
        } else {
            return "$input_column as $output_column";
        }
    }

    public function start_transaction() {
      $this->query('start transaction');
    }

    public function commit() {
      $this->query('commit');
    }

    public function rollback() {
      $this->query('rollback');
    }

    /**
     * Returns the array of column values of the first row which meet the criteria.
     *
     * @param string name of a table
     * @param string SQL condition to fetch the row
     */
    public function get_row($table_name, $where = '') {
      if (!empty($where)) {
        $where = "where $where";
      }
      $this->query("select * from `{$table_name}` {$where}", true);
      $row = $this->fetch_array();

      return $row;
    }

    /**
     * Returns array of rows and their column values which meet the given criteria.
     *
     * @param string name of a table
     * @param string SQL condition to fetch the rows
     * @param string SQL "order by" statement
     *
     * @see get_row
     * @see get_all_rows_query
     * @see get_column_data
     */
    public function get_all_rows($table_name, $params = []) {
      $where = $params['where'] ?? "";
      $having = $params['having'] ?? "";
      $order = $params['order'] ?? "";
      $group = $params['group'] ?? "";
      $limit_start = $params['limit_start'] ?? "";
      $limit_end = $params['limit_end'] ?? "";
      $summary_settings = $params['summary_settings'] ?? "";
      // $left_join = $params['left_join'] ?? "";
      $count_rows = $params['count_rows'] ?? FALSE;

      if (!is_array($this->tables[$table_name])) {
        $error = "Trying to get_all_rows from undefined table $table_name <hr>"._print_r(debug_backtrace(false), true);
        _d_echo("DB", "", $error);
        exit($error);
      } else {
        $summaryColumns = [];
        $virtualColumns = [];
        $codeListColumns = [];
        $summaryColumnsSubselect = [];
        $leftJoins = [];

        if (_count($summary_settings)) {
          foreach ($summary_settings as $col_name => $func) {
            $summaryColumns[] = $this->aggregate('sumtable.'.$col_name, $col_name, $func);
          }

          $group2 = '';
          foreach ($summary_settings as $col_name => $sql_func) {
            if ('group' == $sql_func) {
              $group2 .= "$table_name.$col_name, ";
            }
          }
          $group2 = substr($group2, 0, -2);
          if ('' == $group) {
            $group = $group2;
          } else {
            $group = "$group".('' != $group2 ? ", $group2" : '');
          }
        }

        foreach ($this->tables[$table_name] as $col_name => $col_definition) {
          if (
            $col_definition['virtual']
            && !empty($col_definition['sql'])
            && !_count($col_definition['enum_values'])
          ) {
            $virtualColumns[] = "({$col_definition['sql']}) as {$col_name}";

          } else if ($col_definition['type'] == 'lookup') {
            $lookupModel = $this->adios->getModel($col_definition['model']);
            $lookupTable = $lookupModel->getFullTableSqlName();
            $lookupTableAlias = "lookup_{$lookupTable}_{$col_name}";
            $lookupSqlValue = $lookupModel->lookupSqlValue($lookupTableAlias);

            $virtualColumns[] = "({$lookupSqlValue}) as {$col_name}_lookup_sql_value";
            $leftJoins[] = "
              left join
                `{$lookupTable}` as `{$lookupTableAlias}`
                on `{$lookupTableAlias}`.`id` = `{$table_name}`.`{$col_name}`
            ";
          }

          if ('int' == $col_definition['type'] && is_array($col_definition['code_list'])) {
            $tmp_sql = "case ({$table_name}.{$col_name}) ";
            foreach ($col_definition['code_list'] as $tmp_key => $tmp_value) {
              $tmp_sql .= "when {$tmp_key} then '{$tmp_value}' ";
            }
            $tmp_sql .= ' end';

            $codeListColumns[] = "({$tmp_sql}) as {$col_name}, {$table_name}.{$col_name} as {$col_name}_raw";
          }

          if (('int' == $col_definition['type'] || 'varchar' == $col_definition['type']) && is_array($col_definition['enum_values'])) {
            if ($col_definition['virtual']) {
              $tmp_sql = "case ({$col_definition['sql']}) ";
            } else {
              $tmp_sql = "case ({$table_name}.{$col_name}) ";
            }
            foreach ($col_definition['enum_values'] as $tmp_key => $tmp_value) {
              $tmp_sql .= "when '{$tmp_key}' then '{$tmp_value}' ";
            }
            $tmp_sql .= ' end';

            $codeListColumns[] = "({$tmp_sql}) as {$col_name}_enum_value";
            if ($col_definition['virtual']) {
              $codeListColumns[] = "({$col_definition['sql']}) as {$col_name}";
            } else {
              $codeListColumns[] = "{$table_name}.{$col_name} as {$col_name}";
            }
          } else if (
            !$this->tables[$table_name][$col_name]['virtual']
            && 'none' != $this->tables[$table_name][$col_name]['type']
          ) {
            $summaryColumnsSubselect .= "{$table_name}.{$col_name}";
          }
        }

        if ('' != $where) {
          $where = "where $where";
        }
        if ('' != $having) {
          $having = "having $having";
        }
        if ('' != $order) {
          $order = "order by $order";
        }
        if ('' != $group) {
          $group = "group by $group";
        }
        if ('' != $limit_start) {
          $limit = "limit $limit_start";
          if ('' != $limit_end) {
            $limit .= ", $limit_end";
          }
        }

        if (_count($summaryColumns)) {
          $query = "
            select
              ".join(", ", ["0 as dummy"] + $summaryColumns)."
            from (
              select
                ".join(", ", array_merge($summaryColumnsSubselect, $virtualColumns, $codeListColumns))."
              from $table_name
              ".join(" ", $leftJoins)."
              $where
              $group
              $having
              $order
              $limit
            ) as sumtable
          ";
        } else {
          $selectItems = array_merge(["{$table_name}.*"], $virtualColumns, $codeListColumns);

          $query = "
            select
              ".join(", ", $selectItems)."
            from $table_name
            ".join(" ", $leftJoins)."
            $where
            $group
            $having
            $order
            $limit
          ";
        }

        $this->query($query);

        $rows = [];
        $count = 0;
        while ($row = $this->fetch_array()) {
          if ($count_rows) { $count++; }
          else { $rows[] = $row; }
        }

        return ($count_rows ? $count : $rows);
      }
    }

    public function count_all_rows($table_name, $params = []) {
      return $this->get_all_rows($table_name, ['count_rows' => TRUE] + $params);
    }

    /**
     * Returns a string representing a "join" part of a MySQL query.
     * Function is used by get_all_rows_joined.
     *
     * @param string name of a table
     *
     * @see get_all_rows_joined
     * @see get_all_rows
     * @see get_column_data
     */
//     public function get_join_strings($table_name, $join_def)
//     {
//         $joined_alias_tables = [];
//         $join = '';
//         $select = '';

//         $i = 1;
//         foreach ($join_def as $key => $value) {
//             // toto sklada nasledovne query:
//             // pre select cast vlozi pre kazdy pozadovany join dany stlpec, kt. sa ma
//             // vytiahnut z tabulky: <tabulka_alternativny_nazov>.<stlec_obsahujuci_data> as <pozadovany_vystupny_nazov_stlpca>
//             //
//             // potom generuje "left join" statement, tiez pre vsetky pozadovane joiny.
//             //   left join <tabulka> <tabulka_alternativny_nazov> on <tabulka_alternativny_nazov>.id=$table_name.<stlec_obsahujuci_data>
//             //
//             // alternativny nazov tabulky je tu preto, lebo keby som chcel vytahovat udaje z jednej tabulky na zaklade
//             // roznych ID (napr. pre rozne lookup stlpce, kt. sa ale odkazuju na jednu tabulku), tak by vypisal chybu, ze
//             // "table name not unique" => musim zadefinovat alternativny nazov tabulky...
//             //
//             // je tu zakomponovana aj optimalizacia: ak sa z jednej lookup tabulky nacitava viac stlpcov, generuje
//             // sa k tejto lookup tabulke iba jeden "left join on"

//             $pom = explode(' as ', $value);
//             $pom1 = explode('.', trim($pom[0]));
//             $col_name = trim($pom1[0]);
//             $lookup_col_name = trim($pom1[1]);
//             $as_col_name = trim($pom[1]);
//             $lookup_table = $this->tables[$table_name][$col_name]['table'];
//             $lookup_table_2 = "{$lookup_table}_{$i}";

//             $lookup_table_alias = "{$lookup_table}_{$col_name}";

//             if ($res = 1 == preg_match_all("/(.+)\.(.+) as (.+) join on (.+)\.(.+)/", $value, $out)) {
//                 $col_name = $out[1][0];
//                 $lookup_col_name = $out[2][0];
//                 $as_col_name = $out[3][0];
//                 $lookup_table = $out[4][0];
//                 $join_on_col_name = $out[5][0];
//                 $join_type = 'left';
// //        echo "$col_name, $lookup_col_name, $as_col_name, $join_on_col_name, $join_type, $lookup_table, $lookup_table_alias<br/>";
//             } else {
//                 $res = preg_match_all("/(.+)\.(.+) as (.+)/", $value, $out);
//                 $col_name = $out[1][0];
//                 $lookup_col_name = $out[2][0];
//                 $as_col_name = $out[3][0];
//                 $join_on_col_name = $this->tables[$table_name][$col_name]['key']; // nie iba cez "id"
//                 $join_type = 'left';
//             }

//             if ('lookup' == $this->tables[$table_name][$col_name]['type']) {
//                 $select .= ",\n{$lookup_table_alias}.{$lookup_col_name} as {$as_col_name}";
//                 if (!in_array($lookup_table_alias, $joined_alias_tables)) {
//                     $join .= " {$join_type} join {$lookup_table} {$lookup_table_alias} on {$lookup_table_alias}.{$join_on_col_name}={$table_name}.{$col_name}\n";
//                 }
//             }

//             $joined_alias_tables[] = $lookup_table_alias;

//             ++$i;
//         }

//         return [$select, $join];
//     }

    /**
     * Returns array of rows and their column values which meet the given criteria.
     * Acts the same as get_all_rows, but builds SQL query with "join" statements
     * for all lookup columns.
     *
     * @param string name of a table
     * @param string SQL condition to fetch the rows
     * @param string SQL "order by" statement
     *
     * @see get_all_rows
     * @see get_column_data
     */
    // public function get_all_rows_joined($table_name, $join_def, $params)
    // {
    //     extract($params, EXTR_OVERWRITE);

    //     _d_echo("DB", "", 'get_all_rows_joined: '.print_r($join_def, true));

    //     $summary_columns = '';
    //     if (_count($summary_settings)) {
    //         foreach ($summary_settings as $col_name => $func) {
    //             if ($this->tables[$table_name][$col_name]['virtual']) {
    //                 $summary_columns .= $this->aggregate($this->tables[$table_name][$col_name]['sql'], $col_name, $func).', ';
    //             } else {
    //                 $summary_columns .= $this->aggregate($table_name.'.'.$col_name, $col_name, $func).', ';
    //             }
    //         }
    //         if ('' != $summary_columns) {
    //             $summary_columns = substr(", $summary_columns", 0, -2);
    //         }

    //         $group2 = '';
    //         foreach ($summary_settings as $col_name => $sql_func) {
    //             if ('group' == $sql_func) {
    //                 $group2 .= "$table_name.$col_name, ";
    //             }
    //         }
    //         $group2 = substr($group2, 0, -2);
    //         if ('' == $group) {
    //             $group = $group2;
    //         } else {
    //             $group = "$group".('' != $group2 ? ", $group2" : '');
    //         }
    //     } else {
    //         $virtual_columns = '';
    //         if (is_array($this->tables[$table_name])) {
    //             foreach ($this->tables[$table_name] as $col_name => $col_definition) {
    //                 if (('virtual' == $col_definition['type'] || $col_definition['virtual']) && '' != $col_definition['sql'] && !_count($col_definition['enum_values'])) {
    //                     $virtual_columns .= "({$col_definition['sql']}) as {$col_name}, ";
    //                 }
    //                 if ('lookup' == $col_definition['type'] && '' != $col_definition['sql']) {
    //                     $col_definition['sql'] = str_replace('{%TABLE%}', "lookup_{$col_definition['table']}_{$col_name}", $col_definition['sql']);
    //                     $virtual_columns .= "({$col_definition['sql']}) as {$col_name}_lookup_sql_value, ";
    //                     $params['left_join'] .= " left join {$col_definition['table']} as lookup_{$col_definition['table']}_{$col_name} on lookup_{$col_definition['table']}_{$col_name}.{$col_definition['key']} = $table_name.{$col_name} ";
    //                 }
    //             }
    //         }
    //         if ('' != $virtual_columns) {
    //             $virtual_columns = substr(", $virtual_columns", 0, -2);
    //         }
    //     }

    //     $code_list_columns = '';
    //     if (is_array($this->tables[$table_name])) {
    //         foreach ($this->tables[$table_name] as $col_name => $col_definition) {
    //             if ('int' == $col_definition['type'] && is_array($col_definition['code_list'])) {
    //                 $tmp_sql = "case ({$table_name}.{$col_name}) ";
    //                 foreach ($col_definition['code_list'] as $tmp_key => $tmp_value) {
    //                     $tmp_sql .= "when {$tmp_key} then '{$tmp_value}' ";
    //                 }
    //                 $tmp_sql .= ' end';

    //                 $code_list_columns .= "({$tmp_sql}) as {$col_name}, {$table_name}.{$col_name} as {$col_name}_raw, ";
    //             }
    //         }
    //     }

    //     if (is_array($this->tables[$table_name])) {
    //         foreach ($this->tables[$table_name] as $col_name => $col_definition) {
    //             if (('int' == $col_definition['type'] || 'varchar' == $col_definition['type']) && is_array($col_definition['enum_values'])) {
    //                 if ($col_definition['virtual']) {
    //                     $tmp_sql = "case ({$col_definition['sql']}) ";
    //                 } else {
    //                     $tmp_sql = "case ({$table_name}.{$col_name}) ";
    //                 }
    //                 foreach ($col_definition['enum_values'] as $tmp_key => $tmp_value) {
    //                     $tmp_sql .= "when '{$tmp_key}' then '{$tmp_value}' ";
    //                 }
    //                 $tmp_sql .= ' end';

    //                 $code_list_columns .= "({$tmp_sql}) as {$col_name}_enum_value,";
    //                 if ($col_definition['virtual']) {
    //                     $code_list_columns .= "({$col_definition['sql']}) as {$col_name}, ";
    //                 } else {
    //                     $code_list_columns .= "{$table_name}.{$col_name} as {$col_name}, ";
    //                 }
    //             }
    //         }
    //     }

    //     if ('' != $code_list_columns) {
    //         $code_list_columns = substr(", $code_list_columns", 0, -2);
    //     }

    //     if ('' != $where) {
    //         $where = "where $where";
    //     }
    //     if ('' != $having) {
    //         $having = "having $having";
    //     }
    //     if ('' != $order) {
    //         $order = "order by $order";
    //     }
    //     if ('' != $group) {
    //         $group = "group by $group";
    //     }
    //     if (is_numeric($limit_start)) {
    //         $limit = "limit $limit_start";
    //         if ('' != $limit_end) {
    //             $limit .= ", $limit_end";
    //         }
    //     }

    //     $join_strings = $this->get_join_strings($table_name, $join_def);
    //     $select = "{$table_name}.* {$join_strings[0]} $summary_columns $virtual_columns $code_list_columns";

    //     if (null != $params['extra_columns']) {
    //         $select .= ", {$params['extra_columns']}";
    //     }

    //     _d_echo("DB", "", "get_all_rows_joined: $table_name, where $where, having $having, order by $order, left_join {$params['left_join']}");

    //     $this->query("select $select from $table_name {$join_strings[1]} {$params['left_join']} $where $group $having $order $limit", true);

    //     if ($count_rows) {
    //         return $this->num_rows();
    //     } else {
    //         $rows = [];
    //         while ($row = $this->fetch_array()) {
    //             $rows[] = $row;
    //         }

    //         return $rows;
    //     }
    // }

    /**
     * Returns array of rows and their column values returned by SQL after
     * executing given query.
     * Uses cached tables to retrieve data for lookups. This
     * drstically decreases number of accesses to the DB.
     *
     * @param string SQL SELECT query to be executed
     *
     * @see get_row
     * @see get_column_data
     */
    public function get_all_rows_query($query, $params = []) {
      $this->query($query);

      $rows = [];

      while ($row = $this->fetch_array()) {
        if ($params['key_column']) {
          $rows[$row[$params['key_column']]] = $row;
        } else {
          $rows[] = $row;
        }
      }

      return $rows;
    }

    public function get_all_rows_query_id($query) {
      return $this->get_all_rows_query($query, ['key_column' => 'id']);
    }

    public function count_all_rows_query($query) {
      $count = 0;
      if ($this->query($query)) {
        $count = $this->num_rows();
      }

      return $count;
    }

    public function walk_all_rows_query($query, $callback) {
      $this->query($query);
      while ($row = $this->fetch_array()) {
        call_user_func_array($callback, [$row]);
      }
    }

    public function _parse_lookup_field($expression)
    {
        $parsed = false;

        if ('' != $expression) {
            $expression_type = (false === strpos($expression, ':') ? '' : trim(strtolower(substr($expression, 0, strpos($expression, ':')))));
            switch ($expression_type) {
                case 'function':
                    list($tmp, $func) = explode(':', $expression);
                    if (is_callable($func)) {
                        $parsed = call_user_func_array($func,
                            ['row' => null]);
                    }
                    break;
                default:
                    $parsed = $expression;
                    break;
            }
        }

        return $parsed;
    }

    public function _parse_sql_where_col_definition($where, $params = [])
    {
        if ('' != $where) {
            $type = (false === strpos($where, ':') ? '' : trim(strtolower(substr($where, 0, strpos($where, ':')))));
            switch ($type) {
                case 'function':
                    list($tmp, $func) = explode(':', $where);
                    if (is_callable($func)) {
                        $where = call_user_func_array($func, [$params]);
                    }
                    break;
                default:
                    $where = $where;
                    break;
            }
        }

        return '' == trim($where) ? 'TRUE' : $where;
    }

    /**
     * Returns the shortened value of a given text parameter. The original text
     * value is cut to the given length and if necessary, the string "..." is added.
     *
     * @param string text to shorten
     * @param string Maixmum output length of a text
     *
     * @see get_item_text
     */
    public function get_short_value($text, $length = 20)
    {
        if (strlen($text) <= $length) {
            return $text;
        } else {
            $pom = explode("\n", wordwrap($text, $length, "\n", 1));

            return $pom[0].' ...';
        }
    }

    public function load_pivot_table($table_name, $params = [])
    {
    }

    public function load_tables_serialized($tag)
    {
        $_tables = null;
        $serialized_fname = "{$this->adios->config['cache_dir']}/{$this->adios->config['version']}_".md5($tag).'.tbl';
        if (file_exists($serialized_fname)) {
            $_tables = unserialize(join('', file($serialized_fname)));
        }

        return $_tables;
    }

    public function save_tables_serialized($_tables, $tag)
    {
        $serialized_fname = "{$this->adios->config['cache_dir']}/{$this->adios->config['version']}_".md5($tag).'.tbl';

        $h = @fopen($serialized_fname, 'w');
        @fwrite($h, serialize($_tables));
        @fclose($h);
    }

    /**
     * Performs check, if user has permissions for $operation in $table
     * returns array with key "allowed" - boolean value - if operations is allowed
     * returns array with key "error" - error string.
     *
     * @param string table name
     * @param string id of entry in table
     * @param string name of operation ('insert', 'update', 'delete', 'select')
     * @param string where condition for operation
     */
    public function has_perms($table, $id, $operation, $data, $where = '')
    {
        return ['allowed' => TRUE];
    }

    /**
     * Creates given columns in table in the SQL database.
     * creates unique keys and adios auto indexes.
     *
     * @param string name of a table
     * @param string name of a column
     *
     * @see _sql_table_create
     */
    public function alter_sql_column($table_name, $col_name, $sql_definition = '')
    {
        $log_status = $this->log_disabled;
        $this->log_disabled = 1;

        $table_name = $this->escape($table_name);
        $col_name = $this->escape($col_name);

        $table_column = $this->tables[$table_name][$col_name];
        $table_params = $this->tables[$table_name]['%%table_params%%'];
        $col_type = trim($table_column['type']);
        $col_size = trim($table_column['byte_size']);
        $col_definitions = trim($table_column['sql_definitions']);

        $table_exists = $this->get_all_rows_query("
          SELECT table_name
          FROM information_schema.tables
          WHERE table_schema = '{$this->db_name}'
          AND table_name = '{$table_name}'
        ");
        $column_exists = $this->get_all_rows_query("
          SELECT *
          FROM information_schema.columns
          WHERE table_schema = '{$this->db_name}'
          AND table_name = '{$table_name}'
          AND column_name = '{$col_name}'
        ");

        if (!isset($this->tables[$table_name])) {
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: table $table_name was not found in INI file!");
            $this->log_disabled = $log_status;

            return false;
        }
        if (!isset($this->tables[$table_name][$col_name])) {
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: column name $table_name.$col_name was not found in INI file!");
            $this->log_disabled = $log_status;

            return false;
        }
        if ($this->tables[$table_name][$col_name]['virtual']) {
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: column name $table_name.$col_name is virtual!");
            $this->log_disabled = $log_status;

            return false;
        }
        if (!isset($this->registered_columns[$col_type])) {
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: unknown column type column name $table_name.$col_name");
            $this->log_disabled = $log_status;

            return false;
        }
        if (!_count($table_exists)) {
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: table $table_name was not found in database!");
            $this->log_disabled = $log_status;

            return false;
        }

        if ('' == $sql_definition) {
            $sql = $this->registered_columns[$col_type]->get_sql_create_string($table_name, $col_name, $table_column);
        } else {
            $sql = " {$col_name} {$sql_definition} ";
        }
        if (_count($column_exists)) {
            $sql = "alter table {$table_name} change column {$col_name} {$sql} ;;\n";
        } else {
            $sql = "alter table {$table_name} add column {$sql} ;;\n";

            if (
                _count($table_params['indexes']['index'][$col_name]) > 0
              && $table_params['indexes']['index'][$col_name]['columns'] == [$col_name]
              && 1 == $table_params['indexes']['index'][$col_name]['adios_autoindex']
            ) {
                $sql .= "alter table {$table_name} add index {$col_name} ({$col_name});;\n";
            }

            if ('lookup' == $table_column['type'] && !$table_column['disable_foreign_key']) {
                $lookupModel = $this->adios->getModel($table_params['model']);

                $sql .= "
                    ALTER TABLE `{$table_name}`
                    ADD CONSTRAINT `fk_".md5($table_name.'_'.$col_name)."`
                    FOREIGN KEY (`{$col_name}`)
                    REFERENCES `".$lookupModel->getFullTableSQLName()."` (`id`);;
                ";
            }
        }

        try {
            $this->multi_query($sql);
            $this->log_disabled = $log_status;

            return true;
        } catch (\ADIOS\Core\DBException $e) {
            $this->log_disabled = $log_status;
            $this->adios->console->log('alter_sql_column ERROR', "alter_sql_column: query error for column name $table_name.$col_name: ".$e->getMessage()." ($sql)");

            return false;
        }
    }
}
