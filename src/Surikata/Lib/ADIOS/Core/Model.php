<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

/**
 * Core implementation of database model. Extends from Eloquent's model and adds own
 * functionalities.
 */
class Model extends \Illuminate\Database\Eloquent\Model {  
  /**
   * ADIOS model's primary key is always 'id'
   *
   * @var string
   */
  protected $primaryKey = 'id';
  
  protected $guarded = [];
  
  /**
   * ADIOS model does not use time stamps
   *
   * @var bool
   */
  public $timestamps = false;
  
  /**
   * Language dictionary for the context of the model
   *
   * @var array
   */
  public $languageDictionary = [];
  
  /**
   * Full name of the model. Useful for getModel() function
   *
   * @var mixed
   */
  var $name = "";

  /**
   * Short name of the model. Useful for debugging purposes
   *
   * @var mixed
   */
  var $shortName = "";
    
  /**
   * Reference to ADIOS object
   *
   * @var mixed
   */
  var $adios;
  
  /**
   * Shorthand for "global table prefix"
   *
   * @var mixed
   */
  var $gtp = "";
    
  /**
   * Name of the table in SQL database. Used together with global table prefix.
   *
   * @var mixed
   */
  var $sqlName = "";
    
  /**
   * URL base for management of the content of the table. If not empty, ADIOS
   * automatically creates URL addresses for listing the content, adding and
   * editing the content.
   *
   * @var mixed
   */
  var $urlBase = "";
    
  /**
   * Readable title for the table listing.
   *
   * @var mixed
   */
  var $tableTitle = "";

  /**
   * Readable title for the form when editing content.
   *
   * @var mixed
   */
  var $formTitleForEditing = "";

  /**
   * Readable title for the form when inserting content.
   *
   * @var mixed
   */
  var $formTitleForInserting = "";

  /**
   * SQL-compatible string used to render displayed value of the record when used
   * as a lookup.
   *
   * @var mixed
   */
  var $lookupSqlValue = "";

  var $pdo;
  var $eloquentQuery;
  var $searchAction;
  
  /**
   * Creates instance of model's object.
   *
   * @param  mixed $adiosOrAttributes
   * @param  mixed $eloquentQuery
   * @return void
   */
  public function __construct($adiosOrAttributes = NULL, $eloquentQuery = NULL) {
    $this->gtp = (empty($adiosOrAttributes->gtp) ? GTP : $adiosOrAttributes->gtp); // GTP konstanta kvoli CASCADE
    $this->table = "{$this->gtp}_{$this->sqlName}";

    if (!is_object($adiosOrAttributes)) {
      // v tomto pripade ide o volanie construktora z Eloquentu
      return parent::__construct($adiosOrAttributes ?? []);
    } else {
      $this->name = str_replace("\\", "/", str_replace("ADIOS\\", "", get_class($this)));
      $this->shortName = end(explode("/", $this->name));

      $this->adios = &$adiosOrAttributes;

      if ($eloquentQuery === NULL) {
        $this->eloquentQuery = $this->select('id');
      } else {
        $this->eloquentQuery = $eloquentQuery;
      }

      $this->eloquentQuery->pdoCrossTables = [];

      $this->pdo = $this->getConnection()->getPdo();

      $this->init();

      $this->adios->db->addTable($this->table, $this->columns());
      $this->adios->addRouting($this->routing());

    }
  }
    
  /**
   * Empty placeholder for callback called after the instance has been created in constructor.
   *
   * @return void
   */
  public function init() { /* to be overriden */ }
  
  /**
   * Shorthand for ADIOS core translate() function. Uses own language dictionary.
   *
   * @param  string $string String to be translated
   * @param  string $context Context where the string is used
   * @param  string $toLanguage Output language
   * @return string Translated string.
   */
  public function translate($string, $context = "", $toLanguage = "") {
    return $this->adios->translate($string, $context, $toLanguage, $this->languageDictionary);
  }
  
  /**
   * Installs the model into SQL database. Automaticaly creates indexes.
   *
   * @return void
   */
  public function install() {
    if (!empty($this->getFullTableSQLName())) {
      $this->adios->db->recreate_sql_table(
        $this->getFullTableSQLName()
      );

      foreach ($this->indexes() as $indexOrConstraintName => $indexDef) {
        if (empty($indexOrConstraintName) || is_numeric($indexOrConstraintName)) {
          $indexOrConstraintName = md5(json_encode($indexDef));
        }

        switch ($indexDef["type"]) {
          case "index":
            $tmpColumns = "";
            foreach ($indexDef['columns'] as $tmpColumnName) {
              $tmpColumns .= ($tmpColumns == "" ? "" : ", ")."`{$tmpColumnName}`";
            }

            $this->adios->db->query("
              alter table `".$this->getFullTableSQLName()."`
              add index `{$indexOrConstraintName}` ({$tmpColumns})
            ");
          break;
          case "unique":
            $tmpColumns = "";

            foreach ($indexDef['columns'] as $tmpColumnName) {
              $tmpColumns .= ($tmpColumns == "" ? "" : ", ")."`{$tmpColumnName}`";
            }

            $this->adios->db->query("
              alter table `".$this->getFullTableSQLName()."`
              add constraint `{$indexOrConstraintName}` unique ({$tmpColumns})
            ");
          break;
        }
      }
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Create foreign keys for the SQL table. Called when all models are installed.
   *
   * @return void
   */
  public function installForeignKeys() {

    if (!empty($this->getFullTableSQLName())) {
      $this->adios->db->create_foreign_keys($this->getFullTableSQLName());
    }

  }

  /**
   * Returns full name of the model's SQL table
   *
   * @return string Full name of the model's SQL table
   */
  public function getFullTableSQLName() {
    return $this->table;
  }
  
  /**
   * Returns full relative URL path for model. Used when generating URL
   * paths for tables, forms, etc...
   *
   * @param  mixed $params
   * @return void
   */
  public function getFullUrlBase($params) {
    $urlBase = $this->urlBase;
    if (is_array($params)) {
      foreach ($params as $key => $value) {
        $urlBase = str_replace("{{ {$key} }}", (string) $value, $urlBase);
      }
    }

    return $urlBase;
  }

  //////////////////////////////////////////////////////////////////
  // misc helper methods

  public function findForeignKeyModels() {
    $foreignKeyModels = [];

    foreach ($this->adios->models as $model) {
      foreach ($model->columns() as $colName => $colDef) {
        if (!empty($colDef["model"]) && $colDef["model"] == $this->name) {
          $foreignKeyModels[$model->name] = $colName;
        }
      }
    }

    return $foreignKeyModels;
  }

  public function getEnumValues() {
    $tmp = $this
      ->selectRaw("{$this->table}.id")
      ->selectRaw("(".str_replace("{%TABLE%}", $this->table, $this->lookupSqlValue()).") as ___lookupSqlValue")
      ->orderByRaw("(".str_replace("{%TABLE%}", $this->table, $this->lookupSqlValue()).")", "asc")
      ->get()
      ->toArray()
    ;

    $enumValues = [];
    foreach ($tmp as $key => $value) {
      $enumValues[$value['id']] = $value['___lookupSqlValue'];
    }

    return $enumValues;
  }

  public function associateKey($input, $key) {
    if (is_array($input)) {
      $output = [];
      foreach ($input as $row) {
        $output[$row[$key]] = $row;
      }
      return $output;
    } else {
      return parent::keyBy($input);
    }
  }

  public function sqlQuery($query) {
    return $this->adios->db->query($query, $this);
  }



  //////////////////////////////////////////////////////////////////
  // routing

  public function routing(array $routing = []) {
    return $this->adios->dispatchEventToPlugins("onModelAfterRouting", [
      "model" => $this,
      "routing" => $this->addStandardCRUDRouting($routing),
    ])["routing"];
  }

  public function addStandardCRUDRouting($routing = []) {
    $urlBase = str_replace('/', '\/', $this->urlBase);
    $urlParams = [];

    $varsInUrl = preg_match_all('/{{ (\w+) }}/', $urlBase, $m);
    foreach ($m[0] as $k => $v) {
      $urlBase = str_replace($v, '([\w\.-]+)', $urlBase);
      $urlParams[$m[1][$k]] = '$'.($k + 1);
    }

    if (!is_array($routing)) {
      $routing = [];
    }

    $routing = array_merge($routing, [
      '/^'.$urlBase.'$/' => [
        "action" => "UI/Table",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
        ])
      ],
      '/^'.$urlBase.'\/(\d+)\/Edit$/' => [
        "action" => "UI/Form",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
          "id" => '$'.($varsInUrl + 1),
        ])
      ],
      '/^'.$urlBase.'\/Add$/' => [
        "action" => "UI/Form",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
          "id" => -1,
        ])
      ],
      '/^'.$urlBase.'\/Search$/' => [
        "action" => "UI/Table/Search",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
          "searchGroup" => $urlBase,
        ])
      ],
    ]);

    return $routing;
  }

  //////////////////////////////////////////////////////////////////
  // definition of columns

  public function columns(array $columns = []) {
    // default column settings
    foreach ($columns as $colName => $colDefinition) {
      if ($colDefinition["type"] == "char") {
        $this->adios->console->log("Model", "{$this->name}, {$colName}: char type is deprecated");
      }

      switch ($colDefinition["type"]) {
        case "int":
          $columns[$colName]["byte_size"] = $columns[$colName]["byte_size"] ?? 8;
        break;
        case "float":
          $columns[$colName]["byte_size"] = $columns[$colName]["byte_size"] ?? 14;
          $columns[$colName]["decimals"] = $columns[$colName]["decimals"] ?? 2;
        break;
        case "varchar":
        case "password":
          $columns[$colName]["byte_size"] = $columns[$colName]["byte_size"] ?? 255;
        break;
        // case "lookup":
        //   try {
        //     $tmpModelClassName = $this->adios->getModelClassName($columns[$colName]["model"]);
        //     $tmpModel = new $tmpModelClassName($this->adios);
        //   } catch (Exception $e) {
        //     throw new \Exception("Model {$this->name}: Failed to initialize lookup column {$colName}.");
        //   }

        //   $columns[$colName]["key"] = $columns[$colName]["key"] ?? "id";
        //   $columns[$colName]["table"] = $columns[$colName]["table"] ?? $tmpModel->getFullTableSQLName();
        //   $columns[$colName]["sql"] = $columns[$colName]["sql"] ?? $tmpModel->lookupSqlValue();

        //   unset($tmpModel);
        // break;
      }
    }

    $columns = $this->adios->dispatchEventToPlugins("onModelAfterColumns", [
      "model" => $this,
      "columns" => $columns,
    ])["columns"];

    $this->fillable = array_keys($columns);

    return $columns;
  }

  public function columnNames() {
    return array_keys($this->columns());
  }

  public function indexes(array $indexes = []) {
    return $this->adios->dispatchEventToPlugins("onModelAfterIndexes", [
      "model" => $this,
      "indexes" => $indexes,
    ])["indexes"];
  }

  public function indexNames() {
    return array_keys($this->indexNames());
  }

  //////////////////////////////////////////////////////////////////
  // CRUD methods

  public function getById(int $id) {
    return reset($this->where('id', $id)->get()->toArray());
  }

  public function getAll(string $keyBy = "") {
    $all = $this->get();
    
    if (!empty($keyBy)) {
      $all = $all->keyBy($keyBy);
    }
    
    return $all->toArray();
  }

  public function getQueryWithLookups($callback = NULL) {
    $query = $this->getQuery();
    $this->addLookupsToQuery($query);

    if ($callback !== NULL && $callback instanceof \Closure) {
      $query = $callback($this, $query);
    }

    return $query;
  }

  public function getWithLookups($callback = NULL) {
    $query = $this->getQueryWithLookups($callback);

    return $this->processLookupsInQueryResult(
      $this->fetchQueryAsArray($query, 'id', FALSE),
      TRUE
    );
  }

  public function insertRow($data) {
    return $this->adios->db->insert_row($this->table, $data);
  }

  public function insertRandomRow($data = [], $dictionary = []) {
    return $this->adios->db->insert_random_row($this->table, $data ,$dictionary);
  }

  public function updateRow($data, $id) {
    return $this->adios->db->update_row_part($this->table, $data, $id);
  }

  public function deleteRow($id) {
    return $this->sqlQuery("
      delete from `{$this->table}`
      where `id` = ".(int) $id."
      limit 1
    ");
  }

  public function search($q) {}

  public function pdoPrepareAndExecute(string $query, array $variables) {
    $q = $this->pdo->prepare(str_replace(":table", $this->getFullTableSQLName(), $query));
    return $q->execute($variables);
  }

  public function pdoPrepareExecuteAndFetch(string $query, array $variables) {
    $q = $this->pdo->prepare(str_replace(":table", $this->getFullTableSQLName(), $query));
    $q->execute($variables);
    return $q->fetchAll(\PDO::FETCH_ASSOC);
  }

  //////////////////////////////////////////////////////////////////
  // lookup processing methods

  public function lookupSqlWhere($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = []) {
    return "TRUE";
  }

  public function lookupSqlOrderBy($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = []) {
    return "`input_lookup_value` asc";
  }

  public function lookupSqlQuery($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = [], $having = "TRUE") {
    return str_replace('{%TABLE%}', $this->table, "
      select
        id,
        ".$this->lookupSqlValue()." as input_lookup_value
      from `{$this->table}`
      where
        ".$this->lookupSqlWhere($initiatingModel, $initiatingColumn, $formData, $params)."
      having
        {$having}
      order by
        ".$this->lookupSqlOrderBy($initiatingModel, $initiatingColumn, $formData, $params)."
    ");
  }

  public function lookupSqlValue($tableAlias = NULL) {
    $value = $this->lookupSqlValue ?? "concat('{$this->name}, id = ', {%TABLE%}.id)";

    return ($tableAlias !== NULL
      ? str_replace('{%TABLE%}', "`{$tableAlias}`", $value)
      : $value
    );
  }

  public function tableParams($params) {
    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function tableRowCSSFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onTableRowCSSFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"]["css"];
  }

  public function tableCellCSSFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onTableCellCSSFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"]["css"];
  }

  public function tableCellHTMLFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onTableCellHTMLFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"]["html"];
  }

  public function tableCellCSVExportFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onTableCellCSVExportFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"]["csv"];
  }

  public function onTableAfterDataLoaded($tableObject) { }

  public function tableFilterColumnSqlWhere($columnName, $filterValue, $column = NULL) {
    if ($column === NULL) {
      $column = $this->columns()[$columnName];
    }

    $type = $column['type'];
    $s = explode(',', $filterValue);
    if (('int' == $type && _count($column['enum_values'])) || 'varchar' == $type || 'text' == $type || 'color' == $type || 'file' == $type || 'image' == $type || 'enum' == $type || 'password' == $type || 'lookup' == $type) {
        $w = explode(' ', $filterValue);
    } else {
        $w = $filterValue;
    }

    if ('' != trim($column['sql']) && 'lookup' != $type) {
        if (!('int' == $type && _count($column['enum_values']))) {
            $columnName = '('.$column['sql'].')';
        }
    }

    $return = 'false';

    // trochu komplikovanejsia kontrola, ale znamena, ze vyhladavany retazec sa pouzije len ak uz nie je delitelny podla ciarok, alebo medzier
    // pripadne tato kontrola eplati ak je na zaciatku =

    if (
        '=' == $filterValue[0]
      || (is_array($s) && 1 == count($s) && is_array($w) && 1 == count($w))
      || (is_array($s) && 1 == count($s) && !is_array($w) && '' != $w)
    ) {
        $s = reset($s);

        if ('=' == $filterValue[0]) {
            $s = substr($filterValue, 1);
        }

        if ('!=' == substr($s, 0, 2)) {
            $not = true;
            $s = substr($s, 2);
        }

        // queryies pre typy

        if ('bool' == $type) {
            if ('Y' == $s) {
                $return = "{$columnName} = '".$this->adios->db->escape(trim($s))."' ";
            } else {
                $return = "({$columnName} != 'Y' OR {$columnName} is null) ";
            }
        }

        if ('boolean' == $type) {
            if ('0' == $s) {
                $return = "({$columnName} = '".$this->adios->db->escape(trim($s))."'  or {$columnName} is null) ";
            } else {
                $return = "{$columnName} != '0'";
            }
        }

        if (('int' == $type && _count($column['enum_values'])) || 'varchar' == $type || 'text' == $type || 'color' == $type || 'file' == $type || 'image' == $type || 'enum' == $type || 'password' == $type) {
            $return = " {$columnName} like '%".$this->adios->db->escape(trim($s))."%'";
        }

        if ('lookup' == $type) {
            $return = " {$columnName}_lookup_sql_value like '%".$this->adios->db->escape(trim($s))."%'";
        }

        if ('float' == $type || ('int' == $type && !_count($column['enum_values']))) {
            $s = trim(str_replace(',', '.', $s));
            $s = str_replace(' ', '', $s);

            if (is_numeric($s)) {
                $return = "({$columnName}=$s)";
            } elseif ('-' != $s[0] && strpos($s, '-')) {
                list($from, $to) = explode('-', $s);
                $return = "({$columnName}>=".(trim($from) + 0)." and {$columnName}<=".(trim($to) + 0).')';
            } elseif (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? trim($m[1]) : '=');
                $operand = trim($m[2]) + 0;
                $return = "{$columnName} {$operator} {$operand}";
            } else {
                $return = 'FALSE';
            }
        }

        if ('date' == $type) {
            $s = str_replace(' ', '', $s);
            $s = str_replace(',', '.', $s);

            $return = 'false';

            // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
            if ($s === '-') {
                $return = "({$columnName} IS NULL OR {$columnName} = '0000-00-00' OR {$columnName} = '')";
            }

            if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                if (strtotime($m[2]) > 0) {
                    $to = date('Y-m-d', strtotime($m[2]));
                    $return = "{$columnName} {$operator} '{$to}'";
                } else {
                  //
                }
            }
            if (preg_match('/^([\>\<=\!]{1,2})([0-9\.\-]+)([\>\<=\!]{1,2})([0-9\.\-]+)$/', $s, $m)) {
                $operator_1 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                $date_1 = date('Y-m-d', strtotime($m[2]));
                $operator_2 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[3] : '=');
                $date_2 = date('Y-m-d', strtotime($m[4]));
                if (strtotime($m[2]) > 0 && strtotime($m[4]) > 0) {
                    $return = "({$columnName} {$operator_1} '{$date_1}') and ({$columnName} {$operator_2} '{$date_2}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9\.\-]+)-([0-9\.\-]+)$/', $s, $m)) {
                $date_1 = date('Y-m-d', strtotime($m[1]));
                $date_2 = date('Y-m-d', strtotime($m[2]));
                if (strtotime($m[1]) > 0 && strtotime($m[2]) > 0) {
                    $return = "({$columnName} >= '{$date_1}') and ({$columnName} <= '{$date_2}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9]+)\.([0-9]+)$/', $s, $m)) {
                $month = $m[1];
                $year = $m[2];
                $return = "(month({$columnName}) = '{$month}') and (year({$columnName}) = '{$year}')";
            }
            if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                $year = $m[2];
                $return = "(year({$columnName}) {$operator} '{$year}')";
            }
        }

        if ('datetime' == $type || 'timestamp' == $type) {
            $s = str_replace(' ', '', $s);
            $s = str_replace(',', '.', $s);

            $return = 'false';

            // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
            if ($s === '-') {
                $return = "({$columnName} IS NULL OR {$columnName} = '0000-00-00 00:00:00' OR {$columnName} = '')";
            }

            if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                if (strtotime($m[2]) > 0) {
                    $to = date('Y-m-d', strtotime($m[2]));
                    $return = "date({$columnName}) {$operator} '{$to}'";
                } else {
                  //
                }
            }
            if (preg_match('/^([\>\<=\!]{1,2})([0-9\.\-]+)([\>\<=\!]{1,2})([0-9\.\-]+)$/', $s, $m)) {
                $operator_1 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                $date_1 = date('Y-m-d', strtotime($m[2]));
                $operator_2 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[3] : '=');
                $date_2 = date('Y-m-d', strtotime($m[4]));
                if (strtotime($m[2]) > 0 && strtotime($m[4]) > 0) {
                    $return = "(date({$columnName}) {$operator_1} '{$date_1}') and (date({$columnName}) {$operator_2} '{$date_2}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9\.\-]+)-([0-9\.\-]+)$/', $s, $m)) {
                $date_1 = date('Y-m-d', strtotime($m[1]));
                $date_2 = date('Y-m-d', strtotime($m[2]));
                if (strtotime($m[1]) > 0 && strtotime($m[2]) > 0) {
                    $return = "(date({$columnName}) >= '{$date_1}') and (date({$columnName}) <= '{$date_2}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9]+)\.([0-9]+)$/', $s, $m)) {
                $month = $m[1];
                $year = $m[2];
                $return = "(month({$columnName}) = '{$month}') and (year({$columnName}) = '{$year}')";
            }
            if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                $year = $m[2];
                $return = "(year({$columnName}) {$operator} '{$year}')";
            }
        }

        if ('time' == $type) {
            $return = 'false';
            $s = str_replace(' ', '', $s);

            // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
            if ($s === '-') {
                $return = "({$columnName} IS NULL OR {$columnName} = '00:00:00' OR {$columnName} = '')";
            }

            if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\:]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                if (strtotime('01.01.2000 '.$m[2]) > 0) {
                    $to = date('H:i:s', strtotime('01.01.2000 '.$m[2]));
                    $return = "{$columnName} {$operator} '{$to}'";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9\:]+)-([0-9\:]+)$/', $s, $m)) {
                $date_1 = date('H:i:s', strtotime('01.01.2000 '.$m[1]));
                $date_2 = date('H:i:s', strtotime('01.01.2000 '.$m[2]));
                if (strtotime('01.01.2000 '.$m[1]) > 0 && strtotime('01.01.2000 '.$m[2]) > 0) {
                    $return = "({$columnName} >= '{$date_1}') and ({$columnName} <= '{$date_2}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9]+)$/', $s, $m)) {
                $hour = $m[1];
                $return = "(hour({$columnName}) = '{$hour}')";
            }
        }

        if ('year' == $type) {
            $return = 'false';

            if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
                $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
                if (is_numeric($m[2])) {
                    $return = "{$columnName} {$operator} '$m[2]'";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9\:]+)-([0-9\:]+)$/', $s, $m)) {
                if (is_numeric($m[1]) && is_numeric($m[2])) {
                    $return = "({$columnName} >= '{$m[1]}') and ({$columnName} <= '{$m[2]}')";
                } else {
                  //
                }
            }
            if (preg_match('/^([0-9]+)$/', $s, $m)) {
                $return = "({$columnName} = '{$m[1]}')";
            }
        }

        if ($not) {
            $return = " not( {$return} ) ";
        }
    } elseif (is_array($s) && count($s) > 1) {
        foreach ($s as $val) {
            $wheres[] = $this->tableFilterColumnSqlWhere($columnName, $val, $column);
        }
        $return = implode(' or ', $wheres);
    } elseif (is_array($w) && count($w) > 1) {
        foreach ($w as $val) {
            $wheres[] = $this->tableFilterColumnSqlWhere($columnName, $val, $column);
        }
        $return = implode(' and ', $wheres);
    }

    return $return;

  }

  public function tableFilterSqlWhere($filterValues) {
    $having = "TRUE";

    if (is_array($filterValues)) {
      foreach ($filterValues as $columnName => $filterValue) {
        if (empty($filterValue)) continue;

        if (strpos($columnName, "LOOKUP___") === 0) {
          list($dummy, $srcColumnName, $lookupColumnName) = explode("___", $columnName);
          
          $srcColumn = $this->columns()[$srcColumnName];
          $lookupModel = $this->adios->getModel($srcColumn['model']);

          $having .= " and (".$lookupModel->tableFilterColumnSqlWhere(
            $columnName,
            $filterValue,
            $lookupModel->columns()[$lookupColumnName]
          ).")";

        } else {
          $having .= " and (".$this->tableFilterColumnSqlWhere(
            $columnName,
            $filterValue
          ).")";
        }
      }
    }

    return $having;

  }

  //////////////////////////////////////////////////////////////////
  // UI/Form methods

  public function formParams($data, $params) {
    return $this->adios->dispatchEventToPlugins("onModelAfterFormParams", [
      "model" => $this,
      "data" => $data,
      "params" => $params,
    ])["params"];
  }

  public function formValidate($data) {
    return $this->adios->dispatchEventToPlugins("onModelAfterFormValidate", [
      "model" => $this,
      "data" => $data,
    ])["params"];
  }

  public function formSave($data) {
    try {
      $this->formValidate($data);

      $data = $this->onBeforeSave($data);

      $id = (int) $data['id'];

      if ($id <= 0) {
        $returnValue = $this->insertRow($data);
      } else {
        $returnValue = $this->updateRow($data, $id);
      }

      // POZN. tuto bol kod, ktory zabezpecoval ukladanie udajov stlpca typu
      // 'table'. Ak to budes dorabat, pozri si stary adios, funkcie
      // save_form_catched(), save_form_table_data() a save_form_table_tag_data().

      $returnValue = $this->adios->dispatchEventToPlugins("onModelAfterSave", [
        "model" => $this,
        "data" => $data,
        "returnValue" => $returnValue,
      ])["returnValue"];

      $returnValue = $this->onAfterSave($data, $returnValue);

      return $returnValue;
    } catch (\ADIOS\Core\FormSaveException $e) {
      return $this->adios->renderHtmlWarning($e->getMessage());
    }
  }

  //////////////////////////////////////////////////////////////////
  // UI/Cards methods

  public function cardsParams($params) {
    return $this->adios->dispatchEventToPlugins("onModelAfterCardsParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function cardsCardHtmlFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onModelAfterCardsCardHtmlFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"];
  }

  //////////////////////////////////////////////////////////////////
  // UI/Tree methods

  public function treeParams($params) {
    return $this->adios->dispatchEventToPlugins("onModelAfterTreeParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }


  //////////////////////////////////////////////////////////////////
  // save/delete events

  public function onBeforeSave($data) {
    return $this->adios->dispatchEventToPlugins("onModelBeforeSave", [
      "model" => $this,
      "data" => $data,
    ])["data"];
  }

  public function onAfterSave($data, $returnValue) {
    return $this->adios->dispatchEventToPlugins("onModelAfterSave", [
      "model" => $this,
      "data" => $data,
      "returnValue" => $returnValue,
    ])["returnValue"];
  }

  public function onBeforeDelete($data) {
    return $this->adios->dispatchEventToPlugins("onModelBeforeDelete", [
      "model" => $this,
      "data" => $data,
    ])["data"];
  }

  public function onAfterDelete($data) {
    return $this->adios->dispatchEventToPlugins("onModelAfterDelete", [
      "model" => $this,
      "data" => $data,
    ])["data"];
  }


  //////////////////////////////////////////////////////////////////
  // own implementation of lookups and pivots

  // getQuery
  public function getQuery($columns = NULL) {
    if ($columns === NULL) $columns = $this->table.".id";
    return $this->select($columns);
  }

  // addLookupsToQuery
  public function addLookupsToQuery($query, $lookupsToAdd = NULL) {
    if (empty($query->addedLookups)) {
      $query->addedLookups = [];
    }

    if ($lookupsToAdd === NULL) {
      $lookupsToAdd = [];
      foreach ($this->columns() as $colName => $colDef) {
        if (!empty($colDef['model'])) {
          $tmpModel = $this->adios->getModel($colDef['model']);
          $lookupsToAdd[$colName] = $tmpModel->shortName;
        }
      }
    }

    foreach ($query->addedLookups as $colName => $lookupName) {
      unset($lookupsToAdd[$colName]);
    }

    $selects = [$this->getFullTableSQLName().".*"];
    $joins = [];

    foreach ($lookupsToAdd as $colName => $lookupName) {
      $lookupedModel = $this->adios->getModel($this->columns()[$colName]['model']);

      $selects[] = $lookupedModel->getFullTableSQLName().".id as {$lookupName}___LOOKUP___id";

      foreach ($lookupedModel->columnNames() as $lookupedColName) {
        $selects[] = $lookupedModel->getFullTableSQLName().".{$lookupedColName} as {$lookupName}___LOOKUP___{$lookupedColName}";
      }

      $joins[] = [
        $lookupedModel->getFullTableSQLName(),
        $lookupedModel->getFullTableSQLName().".id",
        '=',
        $this->getFullTableSQLName().".{$colName}"
      ];

      $query->addedLookups[$colName] = $lookupName;
    }

    $query = $query->addSelect($selects);
    foreach ($joins as $join) {
      $query = $query->leftJoin($join[0], $join[1], $join[2], $join[3]);
    }

    return $this;
  }

  // addCrossTableToQuery
  public function addCrossTableToQuery($query, $crossTableModelName, $resultKey = '') {
    $crossTableModel = $this->adios->getModel($crossTableModelName);
    if (empty($resultKey)) {
      $resultKey = $crossTableModel->shortName;
    }

    $foreignKey = "";
    foreach ($crossTableModel->columns() as $crossTableColName => $crossTableColDef) {
      if ($crossTableColDef['model'] == $this->name) {
        $foreignKey = $crossTableColName;
      }
    }

    if (empty($query->pdoCrossTables)) {
      $query->pdoCrossTables = [];
    }
    $query->pdoCrossTables[] = [$crossTableModel, $foreignKey, $resultKey];

    return $this;
  }

  // // convertToEloquentQuery
  // public function convertToEloquentQuery() {
  //   // $tmp = new static($this->adios, $this->eloquentQuery);
  //   // $tmp->eloquentQuery = $this->eloquentQuery;
  //   return $this->eloquentQuery;
  // }

  public function processLookupsInQueryResult($rows) {
    $processedRows = [];
    foreach ($rows as $rowKey => $row) {
      foreach ($row as $colName => $colValue) {
        if (strpos($colName, "___LOOKUP___") !== FALSE) {
          list($tmp1, $tmp2) = explode("___LOOKUP___", $colName);
          $tmp1 = strtoupper($tmp1); // TODO: UPPERCASE LOOKUP
          $row[$tmp1][$tmp2] = $colValue;
          unset($row[$colName]);
        }
      }
      $processedRows[$rowKey] = $row;
    }
    return $processedRows;
  }

  // extractLookupFromQueryResult
  public function extractLookupFromQueryResult($rows, $lookup) {
    $processedRows = [];
    foreach ($rows as $rowKey => $row) {
      $processedRows[$rowKey] = [];
      foreach ($row as $colName => $colValue) {
        if (strpos($colName, "{$lookup}___LOOKUP___") === 0) {
          $processedRows[$rowKey][str_replace("{$lookup}___LOOKUP___", "", $colName)] = $colValue;
        }
      }
    }
    return $processedRows;
  }
  
  // fetchQueryAsArray
  public function fetchQueryAsArray($eloquentQuery, $keyBy = 'id', $processOutput = TRUE) {
    $query = $this->pdo->prepare($eloquentQuery->toSql());
    $query->execute($eloquentQuery->getBindings());

    $rows = $this->associateKey($query->fetchAll(\PDO::FETCH_ASSOC), 'id');

    if ($processOutput) {
      $rows = $this->processLookupsInQueryResult($rows);
    }

    if (!empty($eloquentQuery->pdoCrossTables)) {
      foreach ($eloquentQuery->pdoCrossTables as $crossTable) {
        list($tmpCrossTableModel, $tmpForeignKey, $tmpCrossTableResultKey) = $crossTable;

        $tmpCrossQuery = $tmpCrossTableModel->getQuery();
        $tmpCrossTableModel->addLookupsToQuery($tmpCrossQuery);
        $tmpCrossQuery->whereIn($tmpForeignKey, array_keys($rows));

        $tmpCrossTableValues = $this->fetchQueryAsArray($tmpCrossQuery, 'id', FALSE);

        foreach ($tmpCrossTableValues as $tmpCrossTableValue) {
          $rows
            [$tmpCrossTableValue[$tmpForeignKey]]
            [$tmpCrossTableResultKey]
            [] = $tmpCrossTableValue;
        }

      }
    }

    if (empty($keyBy) || $keyBy === NULL || $keyBy === FALSE || $keyBy == 'id') {
      return $rows;
    } else {
      return $this->associateKey($rows, $keyBy);
    }

  }

}