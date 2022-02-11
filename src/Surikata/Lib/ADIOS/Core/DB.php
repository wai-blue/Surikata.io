<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

// define('_SQL_COLUMN_WHERE_LIKE_BOTH', 'both');
// define('_SQL_COLUMN_WHERE_LIKE_LEFT', 'left');
// define('_SQL_COLUMN_WHERE_LIKE_RIGHT', 'right');

$onkeypress_request = '';

class DB {
    /**
     * Multidimensional array which completely defines the table structure of a database
     * with which will datapub work.
     */
    public $tables;

    public $existingSqlTables = [];
    public $bufferQueries = FALSE;
    public $queryBuffer = "";

    /**
     * Constructor.
     *
     * @param string Name of this element
     * @param array Array of parameters for this module
     */
    public function __construct(&$adios, $params) {
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
      $this->debugCorrectQueries = false;

      $this->tables = [];

      $tmp = $this->get_all_rows_query("show tables", "");
      foreach ($tmp as $value) {
        $this->existingSqlTables[] = reset($value);
      }

      $h = opendir(dirname(__FILE__).'/DB/DataTypes');
      while (false !== ($file = readdir($h))) {
        if ('.' != $file && '..' != $file) {
          $col_type = substr($file, 0, -4);
          $tmp = "m_datapub_column_{$col_type}";
          $this->register_column_type($col_type, $$tmp);
        }
      }
    }

    
    /**
     * Connects the DB object to the database.
     *
     * @throws \ADIOS\Core\Exceptions\DBException When connection string is not configured.
     * @throws \ADIOS\Core\Exceptions\DBException When connection error occured.
     *
     * @return void
     */
    public function connect() {

      if (empty($this->db_host)) {
        throw new \ADIOS\Core\Exceptions\DBException("Database connection string is not configured.");
      }

      if (!empty($this->db_port) && is_numeric($this->db_port)) {
        $this->connection = new \mysqli($this->db_host, $this->db_login, $this->db_password, $this->db_name, $this->db_port);
      } else {
        $this->connection = new \mysqli($this->db_host, $this->db_login, $this->db_password);
      }

      if (!empty($this->connection->connect_error)) {
        throw new \ADIOS\Core\Exceptions\DBException($this->connection->connect_error);
      }

      $this->connection->select_db($this->db_name);

      if ($this->connection->errno == 1049) {
        // unknown database
        $this->query("
          create database if not exists `{$this->db_name}`
          default charset = utf8mb4
          default collate = utf8mb4_unicode_ci
        ");

        $this->adios->console->info("Created database `{$this->db_name}`");

        $this->connection->select_db($this->db_name);

      }

      if (!empty($this->db_codepage)) {
        $this->connection->set_charset($this->db_codepage);
      }

    }

    function escape($str) {
      return $this->connection->real_escape_string((string) $str);
    }


    public function startQueryBuffering() {
      $this->bufferQueries = TRUE;
    }

    public function stopQueryBuffering() {
      $this->bufferQueries = FALSE;
      return $this->getQueryBuffer();
    }

    public function getQueryBuffer() {
      return $this->queryBuffer;
    }

    public function clearQueryBuffer() {
      $this->queryBuffer = "";
    }

    public function addQueryToBuffer($query) {
      $this->queryBuffer .= trim($query, ";").";;\n";
    }

    public function executeBuffer($buffer) {
      $this->multiQuery($buffer, ";;\n");
    }

    /**
     * Runs a single SQL query. Result of a query is stored in a property $db_result.
     * Sets the $db_error property, if an error occurs.
     *
     * @param string SQL query to run
     *
     * @throws \ADIOS\Core\Exceptions\DBDuplicateEntryException When foreign key constrain block the query execution.
     * @throws \ADIOS\Core\Exceptions\DBException In case of any other error.
     *
     * @return object DB result object.
     */
    public function query($query, $initiatingModel = NULL) {
      $query = trim($query, " ;");
      if (empty($query)) return;

      if ($this->bufferQueries) {
        $this->addQueryToBuffer($query);
      };

      $ts1 = _getmicrotime();
      $this->last_query = $query;
      $this->db_result = $this->connection->query($query);
      $this->lastQueryDurationSec = _getmicrotime() - $ts1;

      if (!empty($this->connection->error)) {
        $foreginKeyErrorCodes = [1062, 1216, 1217, 1451, 1452];
        $errorNo = $this->get_error_no();

        if (in_array($errorNo, $foreginKeyErrorCodes)) {
          throw new \ADIOS\Core\Exceptions\DBDuplicateEntryException(
            json_encode([$this->connection->error, $query, $initiatingModel->name, $errorNo])
          );
        } else {
          throw new \ADIOS\Core\Exceptions\DBException("ERROR #: {$errorNo}, ".$this->get_error().", QUERY: {$query}");
        }
      } else {
        if ($this->debugCorrectQueries) {
          $this->adios->console->info("Query OK [".($this->lastQueryDurationSec * 1000)."]:\n{$query}", [], "db");
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
    public function multiQuery($query, $separator = ";;\n", $initiatingModel = NULL) {
      $query = str_replace("\r\n", "\n", $query);
      foreach (explode($separator, $query) as $value) {
        $this->query(trim($value).';', $initiatingModel);
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

    // public function affected_rows($result = null) {
    //   if (!$result) {
    //     $result = $this->db_result;
    //   }

    //   return mysqli_affected_rows($result);
    // }

    public function insert_id() {
      return $this->connection->insert_id;
    }

    // public function check_query($query) {
    //   $regex = '('; // begin group
    //   $regex .= '(?:--|\\#)[\\ \\t\\S]*'; // inline comments
    //   $regex .= '|(?:<>|<=>|>=|<=|==|=|!=|!|<<|>>|<|>|\\|\\||\\||&&|&|-|\\+|\\*(?!\/)|\/(?!\\*)|\\%|~|\\^|\\?)'; // logical operators
    //   $regex .= '|[\\[\\]\\(\\),;`]|\\\'\\\'(?!\\\')|\\"\\"(?!\\"")'; // empty single/double quotes
    //   $regex .= '|".*?(?:(?:""){1,}"|(?<!["\\\\])"(?!")|\\\\"{2})|\'.*?(?:(?:\'\'){1,}\'|(?<![\'\\\\])\'(?!\')|\\\\\'{2})'; // quoted strings
    //   $regex .= '|\/\\*[\\ \\t\\n\\S]*?\\*\/'; // c style comments
    //   $regex .= '|(?:[\\w:@]+(?:\\.(?:\\w+|\\*)?)*)'; // words, placeholders, database.table.column strings
    //   $regex .= '|[\t\ ]+';
    //   $regex .= '|[\.]'; //period
    //   $regex .= '|[\s]'; //whitespace
    //   $regex .= ')'; // end group

    //   // get global match
    //   preg_match_all('/'.$regex.'/smx', $query, $result);

    //   $tokens = [];
    //   foreach ($result[0] as $key => $value) {
    //     if ('' !== trim($value)) {
    //       $tokens[] = $value;
    //     }
    //   }

    //   return ['query' => $query, 'tokens' => $tokens];
    // }

    public function get_error() {
      return $this->connection->error;
    }

    public function get_error_no() {
      return $this->connection->errno;
    }






















    /**
     * Returns html string with initialization. This string should be placed in the <head> tag of the webpage.
     */
    // public function get_init()
    // {
    //     return "";
    // }

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
    public function commonTableColumns($columns = [], $isCrossTable = FALSE) {
      $commonColumns = [];

      if (!$isCrossTable) {
        $commonColumns['id'] = [
          'type' => 'int',
          'byte_size' => '8',
          'sql_definitions' => 'primary key auto_increment',
          'title' => 'ID',
          'only_display' => 'yes',
          'class' => 'primary-key'
        ];
      }

      if (_count($columns)) {
          return array_merge($commonColumns, $columns);
      } else {
        return $commonColumns;
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

    // public function _is_registered_column_type($column_type)
    // {
    //     return $this->is_registered_column_type($column_type);
    // }

    // public function add_created_modified_columns($tables)
    // {
    //     global $gtp;
    //     foreach ($tables as $table_name => $table_columns) {
    //     }

    //     return $tables;
    // }

    /**
     * Returns the array of column parameters out of its string representation.
     *
     * @param array array of column parameters
     */
    // public function _str2col($params)
    // {
    //     $return = [];
    //     $param_arr = explode('|', $params);
    //     foreach ($param_arr as $key => $value) {
    //         $value = trim($value);
    //         $params = explode(' ', $value);
    //         $param_name = $params[0];
    //         array_shift($params);
    //         $param_value = join(' ', $params);

    //         $return[$param_name] = $param_value;
    //     }

    //     return $return;
    // }

    /**
     * Loads table definitions from a filename.
     *
     * @param string filename to load from
     */
    // public function load_tables($filename)
    // {
    //     global $gtp;
    //     if (file_exists($filename)) {
    //         include $filename;
    //         $this->tables = array_merge($this->tables, $_tables);
    //         unset($_tables);
    //     }
    // }

    // public function load_tables_from_dir($dir) {
    //     global $gtp;

    //     if (is_dir($dir)) {
    //         $_tables = [];
    //         foreach (scandir($dir) as $file) {
    //             if (is_file("{$dir}/{$file}")) {
    //                 $_table = NULL;
    //                 include("{$dir}/{$file}");
    //                 if (is_array($_table)) {
    //                     $this->tables["{$gtp}_".str_replace(".php", "", $file)] = $this->commonTableColumns($_table);
    //                 }
    //             }
    //         }
    //     }
    // }

    public function addTable($tableName, $columns, $isCrossTable = FALSE) {
      $this->tables[$tableName] = $this->commonTableColumns($columns, $isCrossTable);
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

      foreach ($table_columns as $col_name => $col_definition) {
        $col_type = trim($col_definition['type']);

        if (isset($this->registered_columns[$col_type]) && !$col_definition['virtual']) {
          $tmp = $this->registered_columns[$col_type]
            ->get_sql_create_string($table_name, $col_name, $col_definition)
          ;
          if (!empty($tmp)) {
            $sql .= "  {$tmp},\n";
          }
        }
      }

      // indexy
      foreach ($table_columns as $col_name => $col_definition) {
        if (
          !$col_definition['virtual']
          && in_array($col_definition['type'], ['lookup', 'int', 'bool', 'boolean', 'date'])
        ) {
          $sql .= "  index `{$col_name}` (`{$col_name}`),\n";
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
            } catch (\ADIOS\Core\Exceptions\DBException $e) {
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
                $this->multiQuery($sql);
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

    public function create_foreign_keys($table) {
      $sql = '';
      foreach ($this->tables[$table] as $column => $columnDefinition) {
        if (
          !$columnDefinition['disable_foreign_key']
          && 'lookup' == $columnDefinition['type']
        ) {
          $lookupModel = $this->adios->getModel($columnDefinition['model']);
          $foreignKeyColumn = $columnDefinition['foreign_key_column'] ?: "id";

          $sql .= "
            ALTER TABLE `{$table}`
            ADD CONSTRAINT `fk_".md5($table.'_'.$column)."`
            FOREIGN KEY (`{$column}`)
            REFERENCES `".$lookupModel->getFullTableSQLName()."` (`{$foreignKeyColumn}`);;
          ";
        }
      }

      if (!empty($sql)) {
        $this->multiQuery($sql);
      }
    }

    /**
     * Returns SQL command which - when executed - fills the SQL table with the data
     * now stored in the database.
     *
     * @param string name of a table to dump
     * @param bool if this param is TRUE, it also creates the table
     */
    // public function dump_data($table_name, $table_create = false)
    // {
    //     $sql = '';

    //     if ($table_create) {
    //         $sql .= $this->_sql_table_create($table_name)."\n\n";
    //     }

    //     $rows = $this->get_all_rows($table_name, ["order" => "id asc"]);
    //     if (is_array($rows)) {
    //         foreach ($rows as $key => $value) {
    //             $sql .= $this->insert_row_query($table_name, $value, $dumping_data = true).";\n";
    //         }
    //     }

    //     return $sql;
    // }

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
    public function _sql_column_data($table, $colName, $data, $dumping_data = false) {
      $colType = $this->tables[$table][$colName]['type'];
      $value = $data[$colName];
      $valueExists = array_key_exists($colName, $data);

      $sql = '';

      // ak je hodnota stlpca definovana ako pole, tak moze mat rozne parametre
      if (is_array($value) && isset($value['sql']) && !empty(trim($value['sql']))) {
        $sql = "`{$colName}` = ({$value['sql']})";
      } else if (strpos((string) $value, "SQL:") === 0) {
        $sql = "`{$colName}` = (".substr($value, 4).")";
      } else if (isset($this->registered_columns[$colType])) {
        $sql = $this->registered_columns[$colType]->get_sql_column_data_string(
          $table,
          $colName,
          $data[$colName],
          [
            'null_value' => !$valueExists,
            'dumping_data' => $dumping_data,
            'data' => $data,
          ]
        );
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
    public function insert_row_query(string $table, array $data, bool $dumpingData = false) {
      $SQL = "";

      $addIdColumn = TRUE;
      if ($dumpingData) $addIdColumn = FALSE;
      if (!isset($this->tables[$table]['id'])) $addIdColumn = FALSE;
      
      if ($addIdColumn) {
        if (!isset($data['id']) || $data['id'] <= 0) {
          $SQL .= "`id`=null, ";
        } else {
          $SQL .= "`id`='".$this->escape($data['id'])."', ";
          unset($data['id']);
        }
      }

      foreach ($this->tables[$table] as $colName => $colDefinition) {
        if (!$colDefinition['virtual'] && $colName != '%%table_params%%') {
          if ($data[$colName] !== NULL) {
            $tmp_sql = $this->_sql_column_data($table, $colName, $data, $dumpingData);

            $SQL .= $tmp_sql;
          } else if (!empty($colDefinition['default_value'])) {
            $SQL .= $colDefinition['default_value'];
          }
        }
      }

      $SQL = substr($SQL, 0, -2);

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
    public function insert_row($table, $data, $only_sql_command = false, $dumping_data = false, $initiatingModel = NULL) {
      if ($data['id'] <= 0) {
        unset($data['id']);
      }

      $sql = "insert into `{$table}` set ";
      $sql .= $this->insert_row_query($table, $data, $dumping_data);

      if ($only_sql_command) {
        return $sql."\n";
      } else {
        $this->multiQuery($sql, ";;\n", $initiatingModel);
        $inserted_id = $this->insert_id();

        return $inserted_id;
      }
    }

    public function insert_or_update_row($table, $data, $only_sql_command = false, $dumping_data = false, $initiatingModel = NULL) {
      if ($data['id'] <= 0) {
        unset($data['id']);
      }

      $dataWithoutId = $data;
      unset($dataWithoutId['id']);

      $sql = "insert into `{$table}` set ";
      $sql .= $this->insert_row_query($table, $data, $dumping_data);
      $sql .= " on duplicate key update ";
      $sql .= $this->insert_row_query($table, $dataWithoutId, TRUE);

      if ($only_sql_command) {
        return $sql."\n";
      } else {
        $this->multiQuery($sql, ";;\n", $initiatingModel);
        $inserted_id = $this->insert_id();

        return $inserted_id;
      }
    }

    public function insert_random_row($table_name, $data = [], $dictionary = [], $initiatingModel = NULL) {
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
                break;
              }
            }
            
            if ($random_val !== NULL) {
              $data[$col_name] = $random_val;
            }
          }
        }
      }
      
      return $this->insert_row($table_name, $data, FALSE, FALSE, $initiatingModel);
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
    public function update_row_query($table_name, $data, $id, $whole_row) {
      global $_FILES;

      if (is_array($_FILES)) {
        foreach ($_FILES as $key => $value) {
          if (null !== $data[$key]) {
            $data[$key] = $value;
          }
        }
      }

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

      $SQL = substr($SQL, 0, -2)." where `id` = $id;";

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
    public function update_row($table_name, $data, $id, $only_sql_command = false, $initiatingModel = NULL) {
      $sql = $this->update_row_query($table_name, $data, $id, TRUE);

      if ($only_sql_command) {
        return $sql;
      } else {
        $this->query($sql, $initiatingModel);
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
    public function update_row_part($table_name, $data, $id, $only_sql_command = FALSE, $initiatingModel = NULL) {
      $sql = $this->update_row_query($table_name, $data, $id, FALSE);

      if ($only_sql_command) {
        return $sql;
      } else {
        $this->query($sql, $initiatingModel);
        return $id;
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

        $log_status = $this->adios->config['log_db']['enabled'];
        $this->adios->config['log_db']['enabled'] = 0;

        $tmp = $this->get_all_rows_query("select id from {$table_name} {$where}");
        foreach ($tmp as $value) {
            $this->delete_row($table_name, $value['id']);
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
        $data = $this->get_row($table_name, "id=$id");

        foreach (array_keys($this->commonTableColumns()) as $col_name) {
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
                    $this->query("update $table_name set $col_name='{$table_name}_{$col_name}_{$inserted_id}.{$ext}' where id=$inserted_id");
                }
                if ('file' == $col_definition['type']) {
                    @copy("{$this->adios->config['files_dir']}/{$data[$col_name]}", "{$this->adios->config['files_dir']}/{$table_name}_{$col_name}_{$data['id']}.{$ext}");
                    $this->query("update $table_name set $col_name='{$table_name}_{$col_name}_{$inserted_id}.{$ext}' where id=$inserted_id");
                }
            }
        }

        return $inserted_id;
    }

//
    ////////////////////////////////////////////////////////////////////////////////////////////////

    // ////////////////////////////////////////////////////////////////////////////////////////////////
    // // move_up, move_down

    // /**
    //  * Moves a row with given ID one row up and returns its ID when succesful.
    //  * Returns -1 if operation was unsuccessful (row was already at the top).
    //  *
    //  * @param string name of a table
    //  * @param int ID of a row to move
    //  */
    // public function move_up($table, $id)
    // {
    //     if (!isset($this->tables[$table]['placement'])) {
    //         return;
    //     }

    //     $all_ids = [];
    //     $current_placement = 0;
    //     $next_placement = 0;
    //     $tmp_prev_placement = 0;
    //     $tmp = $this->get_all_rows_query("select id,placement from {$table} order by placement");
    //     foreach ($tmp as $key => $value) {
    //         $all_ids[] = $value['id'];
    //         if ($value['id'] == $id) {
    //             $current_placement = $value['placement'];
    //             $prev_placement = $tmp_prev_placement;
    //         }

    //         $tmp_prev_placement = $value['placement'];
    //     }

    //     if (0 != $current_placement && 0 != $prev_placement) {
    //         $this->query('start transaction');
    //         $this->query("set @max_placement = (select max(placement) from {$table})");
    //         $this->query("update {$table} set placement=(@max_placement+1) where placement=$current_placement");
    //         $this->query("update {$table} set placement=$current_placement where placement=$prev_placement");
    //         $this->query("update {$table} set placement=$prev_placement where placement=(@max_placement+1)");
    //         $this->query('commit');
    //     }
    // }

    // /**
    //  * Moves a row with given ID one row down and returns its ID when succesful.
    //  * Returns -1 if operation was unsuccessful (row was already on the bottom).
    //  *
    //  * @param string name of a table
    //  * @param int ID of a row to move
    //  */
    // public function move_down($table, $id)
    // {
    //     if (!isset($this->tables[$table]['placement'])) {
    //         return;
    //     }

    //     $all_ids = [];
    //     $current_placement = 0;
    //     $next_placement = 0;
    //     $tmp = $this->get_all_rows_query("select id,placement from {$table} order by placement");
    //     $get_next_placement = false;
    //     foreach ($tmp as $key => $value) {
    //         if ($get_next_placement) {
    //             $next_placement = $value['placement'];
    //         }
    //         $get_next_placement = false;
    //         $all_ids[] = $value['id'];
    //         if ($value['id'] == $id) {
    //             $current_placement = $value['placement'];
    //             $get_next_placement = true;
    //         }
    //     }

    //     if (0 != $current_placement && 0 != $next_placement) {
    //         $this->query('start transaction');
    //         $this->query("set @max_placement = (select max(placement) from {$table})");
    //         $this->query("update {$table} set placement=(@max_placement+1) where placement=$current_placement");
    //         $this->query("update {$table} set placement=$current_placement where placement=$next_placement");
    //         $this->query("update {$table} set placement=$next_placement where placement=(@max_placement+1)");
    //         $this->query('commit');
    //     }
    // }

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

    public function startTransaction() {
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
      $limit_start = (int) ($params['limit_start'] ?? 0);
      $limit_end = (int) ($params['limit_end'] ?? 0);
      $summary_settings = $params['summary_settings'] ?? "";
      // $left_join = $params['left_join'] ?? "";
      $count_rows = $params['count_rows'] ?? FALSE;

      $summaryColumns = [];
      $virtualColumns = [];
      $codeListColumns = [];
      $lookupColumns = [];
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
          if (!$lookupModel->isCrossTable) {
            $lookupTable = $lookupModel->getFullTableSqlName();
            $lookupTableAlias = "lookup_{$lookupTable}_{$col_name}";
            $lookupSqlValue = $lookupModel->lookupSqlValue($lookupTableAlias);

            $virtualColumns[] = "({$lookupSqlValue}) as {$col_name}_lookup_sql_value";
            $leftJoins[] = "
              left join
                `{$lookupTable}` as `{$lookupTableAlias}`
                on `{$lookupTableAlias}`.`id` = `{$table_name}`.`{$col_name}`
            ";

            foreach ($lookupModel->columns() as $lookupColumnName => $lookupColumn) {
              if (!$lookupColumn['virtual']) {
                $lookupColumns[] = "`{$lookupTableAlias}`.`{$lookupColumnName}` as LOOKUP___{$col_name}___{$lookupColumnName}";
              }
            }
          }

        } else if (('int' == $col_definition['type'] || 'varchar' == $col_definition['type']) && is_array($col_definition['enum_values'])) {
          if ($col_definition['virtual']) {
            $tmp_sql = "case (`{$col_definition['sql']}`) ";
          } else {
            $tmp_sql = "case (`{$table_name}`.`{$col_name}`) ";
          }

          foreach ($col_definition['enum_values'] as $tmp_key => $tmp_value) {
            if ($tmp_key === NULL) {
              $tmp_sql .= "when {$tmp_key} then '".$this->escape($tmp_value)."' ";
            } else if (is_numeric($tmp_key)) {
              $tmp_sql .= "when ".((int) $tmp_key)." then '".$this->escape($tmp_value)."' ";
            } else {
              $tmp_sql .= "when '".$this->escape((string) $tmp_key)."' then '".$this->escape($tmp_value)."' ";
            }
          }

          $tmp_sql .= " end";

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
      if ($limit_start > 0 || $limit_end > 0) {
        $limit = "limit $limit_start";
        if ($limit_end > 0) {
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
        if ($count_rows && empty($where)) {
          $selectItems = ["{$table_name}.*"];
        } else {
          $selectItems = array_merge(["{$table_name}.*"], $virtualColumns, $codeListColumns, $lookupColumns);
        }

        $query = "
          select
            ".join(",\n            ", $selectItems)."
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

    public function count_all_rows($table_name, $params = []) {
      return $this->get_all_rows($table_name, ['count_rows' => TRUE] + $params);
    }

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
    public function get_all_rows_query($query, $keyBy = "id") {
      $this->query($query);

      $rows = [];

      while ($row = $this->fetch_array()) {
        if (empty($keyBy)) {
          $rows[] = $row;
        } else {
          $rows[$row[$keyBy]] = $row;
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

    // public function walk_all_rows_query($query, $callback) {
    //   $this->query($query);
    //   while ($row = $this->fetch_array()) {
    //     call_user_func_array($callback, [$row]);
    //   }
    // }

    // public function _parse_lookup_field($expression)
    // {
    //     $parsed = false;

    //     if ('' != $expression) {
    //         $expression_type = (false === strpos($expression, ':') ? '' : trim(strtolower(substr($expression, 0, strpos($expression, ':')))));
    //         switch ($expression_type) {
    //             case 'function':
    //                 list($tmp, $func) = explode(':', $expression);
    //                 if (is_callable($func)) {
    //                     $parsed = call_user_func_array($func,
    //                         ['row' => null]);
    //                 }
    //                 break;
    //             default:
    //                 $parsed = $expression;
    //                 break;
    //         }
    //     }

    //     return $parsed;
    // }

    // public function _parse_sql_where_col_definition($where, $params = [])
    // {
    //     if ('' != $where) {
    //         $type = (false === strpos($where, ':') ? '' : trim(strtolower(substr($where, 0, strpos($where, ':')))));
    //         switch ($type) {
    //             case 'function':
    //                 list($tmp, $func) = explode(':', $where);
    //                 if (is_callable($func)) {
    //                     $where = call_user_func_array($func, [$params]);
    //                 }
    //                 break;
    //             default:
    //                 $where = $where;
    //                 break;
    //         }
    //     }

    //     return '' == trim($where) ? 'TRUE' : $where;
    // }

    // /**
    //  * Returns the shortened value of a given text parameter. The original text
    //  * value is cut to the given length and if necessary, the string "..." is added.
    //  *
    //  * @param string text to shorten
    //  * @param string Maixmum output length of a text
    //  *
    //  * @see get_item_text
    //  */
    // public function get_short_value($text, $length = 20)
    // {
    //     if (strlen($text) <= $length) {
    //         return $text;
    //     } else {
    //         $pom = explode("\n", wordwrap($text, $length, "\n", 1));

    //         return $pom[0].' ...';
    //     }
    // }

    // public function load_pivot_table($table_name, $params = [])
    // {
    // }

    // public function load_tables_serialized($tag)
    // {
    //     $_tables = null;
    //     $serialized_fname = "{$this->adios->config['cache_dir']}/{$this->adios->config['version']}_".md5($tag).'.tbl';
    //     if (file_exists($serialized_fname)) {
    //         $_tables = unserialize(join('', file($serialized_fname)));
    //     }

    //     return $_tables;
    // }

    // public function save_tables_serialized($_tables, $tag)
    // {
    //     $serialized_fname = "{$this->adios->config['cache_dir']}/{$this->adios->config['version']}_".md5($tag).'.tbl';

    //     $h = @fopen($serialized_fname, 'w');
    //     @fwrite($h, serialize($_tables));
    //     @fclose($h);
    // }

    // /**
    //  * Performs check, if user has permissions for $operation in $table
    //  * returns array with key "allowed" - boolean value - if operations is allowed
    //  * returns array with key "error" - error string.
    //  *
    //  * @param string table name
    //  * @param string id of entry in table
    //  * @param string name of operation ('insert', 'update', 'delete', 'select')
    //  * @param string where condition for operation
    //  */
    // public function has_perms($table, $id, $operation, $data, $where = '')
    // {
    //     return ['allowed' => TRUE];
    // }
}
