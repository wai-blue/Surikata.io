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
  // public $languageDictionary = [];
  
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

  /**
   * If set to TRUE, the SQL table will not contain the ID autoincrement column
   *
   * @var mixed
   */
  var $isCrossTable = FALSE;

  var $pdo;
  var $eloquentQuery;
  var $searchAction;

  private static $allItemsCache = NULL;
  
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

      $this->myRootFolder = str_replace("\\", "/", dirname((new \ReflectionClass(get_class($this)))->getFileName()));

      if ($eloquentQuery === NULL) {
        $this->eloquentQuery = $this->select('id');
      } else {
        $this->eloquentQuery = $eloquentQuery;
      }

      $this->eloquentQuery->pdoCrossTables = [];

      $this->pdo = $this->getConnection()->getPdo();

      // During the installation no SQL tables exist. If child's init() 
      // method uses data from DB, $this->init() call would fail.
      try {
        $this->init();
      } catch (\Exception $e) {
        //
      }

      $this->adios->db->addTable($this->table, $this->columns(), $this->isCrossTable);
      $this->adios->addRouting($this->routing());

    }

    if ($this->hasAvailableUpgrades()) {
      $this->adios->userNotifications->addHtml("
        Model <b>{$this->name}</b> has new upgrades available.
        <a
          href='javascript:void(0)'
          onclick='desktop_update(\"Desktop/InstallUpgrades\");'
        >Install upgrades</a>
      ");
    } else if (!$this->hasSqlTable()) {
      $this->adios->userNotifications->addHtml("
        Model <b>{$this->name}</b> has no SQL table.
        <a
          href='javascript:void(0)'
          onclick='desktop_update(\"Desktop/InstallUpgrades\");'
        >Create table</a>
      ");
    } else if (!$this->isInstalled()) {
      $this->adios->userNotifications->addHtml("
        Model <b>{$this->name}</b> is not installed.
        <a
          href='javascript:void(0)'
          onclick='desktop_update(\"Desktop/InstallUpgrades\");'
        >Install model</a>
      ");
    }

  }
    
  /**
   * Empty placeholder for callback called after the instance has been created in constructor.
   *
   * @return void
   */
  public function init() { /* to be overriden */ }

  /**
   * Retrieves value of configuration parameter.
   *
   * @return void
   */
  public function getConfig(string $configName) : string {
    return $this->adios->config['models'][str_replace("/", "-", $this->name)][$configName] ?? "" ;
  }
  
  /**
   * Sets the value of configuration parameter.
   *
   * @return void
   */
  public function setConfig(string $configName, $value) : void {
    $this->adios->config['models'][str_replace("/", "-", $this->name)][$configName] = $value;
  }

  /**
   * Persistantly saves the value of configuration parameter to the database.
   *
   * @return void
   */
  public function saveConfig(string $configName, $value) : void {
    $this->adios->saveConfig([
      "models" => [
        str_replace("/", "-", $this->name) => [
          $configName => $value,
        ],
      ],
    ]);
  }
  
  /**
   * Shorthand for ADIOS core translate() function. Uses own language dictionary.
   *
   * @param  string $string String to be translated
   * @param  string $context Context where the string is used
   * @param  string $toLanguage Output language
   * @return string Translated string.
   */
  public function translate($string) {
    return $this->adios->translate($string, $this);
  }

  public function hasSqlTable() {
    return in_array($this->table, $this->adios->db->existingSqlTables);
  }
  
  /**
   * Checks whether model is installed.
   *
   * @return bool TRUE if model is installed, otherwise FALSE.
   */
  public function isInstalled() : bool {
    return $this->getConfig('installed-version') != "";
  }

  /**
   * Gets the current installed version of the model. Used during installing upgrades.
   *
   * @return void
   */
  public function getCurrentInstalledVersion() : int {
    return (int) ($this->getConfig('installed-version') ?? 0);
  }

  /**
   * Returns list of available upgrades. This method must be overriden by each model.
   *
   * @return array List of available upgrades. Keys of the array are simple numbers starting from 1.
   */
  public function upgrades() : array {
    return [
      0 => [], // upgrade to version 0 is the same as installation
    ];
  }

  /**
   * Installs the first version of the model into SQL database. Automaticaly creates indexes.
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
          $indexOrConstraintName = md5(json_encode($indexDef).uniqid());
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

      $this->saveConfig('installed-version', max(array_keys($this->upgrades())));

      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function hasAvailableUpgrades() : bool {
    $currentVersion = $this->getCurrentInstalledVersion();
    $lastVersion = max(array_keys($this->upgrades()));
    return ($lastVersion > $currentVersion);
  }

  /**
   * Installs all upgrades of the model. Internaly stores current version and
   * compares it to list of available upgrades.
   *
   * @throws \ADIOS\Core\Exceptions\DBException When an error occured during the upgrade.
   * @return void
   */
  public function installUpgrades() : void {
    if ($this->hasAvailableUpgrades()) {
      $currentVersion = $this->getCurrentInstalledVersion();
      $lastVersion = max(array_keys($this->upgrades()));

      try {
        $this->adios->db->startTransaction();

        $upgrades = $this->upgrades();

        for ($v = $currentVersion + 1; $v <= $lastVersion; $v++) {
          if (is_array($upgrades[$v])) {
            foreach ($upgrades[$v] as $query) {
              $this->adios->db->query($query);
            }
          }
        }

        $this->adios->db->commit();
        $this->saveConfig('installed-version', $lastVersion);

      } catch(\ADIOS\Core\Exceptions\DBException $e) {
        $this->adios->db->rollback();
        throw new \ADIOS\Core\Exceptions\DBException($e->getMessage());
      }
    }
  }

  public function dropTableIfExists() {
    $this->adios->db->query("set foreign_key_checks = 0");
    $this->adios->db->query("drop table if exists `".$this->getFullTableSQLName()."`");
    $this->adios->db->query("set foreign_key_checks = 1");
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
      ->orderBy("___lookupSqlValue", "asc")
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
          "searchGroup" => $this->tableTitle ?? $urlBase,
        ])
      ],
      '/^'.$urlBase.'\/Export\/CSV$/' => [
        "action" => "UI/Table/Export/CSV",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
        ])
      ],
      '/^'.$urlBase.'\/Import\/CSV$/' => [
        "action" => "UI/Table/Import/CSV",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
        ])
      ],
      '/^'.$urlBase.'\/Import\/CSV\/Import$/' => [
        "action" => "UI/Table/Import/CSV/Import",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
        ])
      ],
      '/^'.$urlBase.'\/Import\/CSV\/DownloadTemplate$/' => [
        "action" => "UI/Table/Import/CSV/DownloadTemplate",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
        ])
      ],
      '/^'.$urlBase.'\/Import\/CSV\/Preview$/' => [
        "action" => "UI/Table/Import/CSV/Preview",
        "params" => array_merge($urlParams, [
          "model" => $this->name,
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
        $this->adios->console->info("{$this->name}, {$colName}: char type is deprecated");
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

  public function getRelationships() {
    return $this; // to be overriden, should return chained Eloquent's ->with() method calls
  }

  public function getExtendedData($item) {
    return NULL; // to be overriden, should return $item with extended information
    // the NULL return is for optimization in getAll() method
  }

  public function getById(int $id) {
    $item = reset($this->getRelationships()->where('id', $id)->get()->toArray());

    if ($this->getExtendedData([]) !== NULL) {
      $item = $this->getExtendedData($item);
    }

    $item = $this->adios->dispatchEventToPlugins("onModelAfterGetExtendedData", [
      "model" => $this,
      "item" => $item,
    ])["item"];

    return $item;
  }

  public function getByLookupSqlValue(string $lookupSqlValue) {
    return reset($this->adios->db->get_all_rows_query("
      select
        id,
        ".$this->lookupSqlValue("t")." as `input_lookup_value`
      from `{$this->table}` t
      having `input_lookup_value` = '".$this->adios->db->escape($lookupSqlValue)."'
    "));
  }

  public function getAll(string $keyBy = "id", $withLookups = FALSE, $processLookups = FALSE) {
    if ($withLookups) {
      $items = $this->getWithLookups(NULL, $keyBy, $processLookups);
    } else {
      $items = $this->pdoPrepareExecuteAndFetch("select * from :table", [], $keyBy);
    }

    if ($this->getExtendedData([]) !== NULL) {
      foreach ($items as $key => $item) {
        $items[$key] = $this->getExtendedData($item);
      }
    }
    
    return $items;
  }

  public function getAllCached() {
    if (static::$allItemsCache === NULL) {
      static::$allItemsCache = $this->getAll();
    }

    return static::$allItemsCache;
  }

  public function getQueryWithLookups($callback = NULL) {
    $query = $this->getQuery();
    $this->addLookupsToQuery($query);

    if ($callback !== NULL && $callback instanceof \Closure) {
      $query = $callback($this, $query);
    }

    return $query;
  }

  public function getWithLookups($callback = NULL, $keyBy = 'id', $processLookups = FALSE) {
    $query = $this->getQueryWithLookups($callback);
    return $this->processLookupsInQueryResult(
      $this->fetchRows($query, $keyBy, FALSE),
      $processLookups
    );
  }

  public function insertRow($data) {
    return $this->adios->db->insert_row($this->table, $data, FALSE, FALSE, $this);
  }

  public function insertOrUpdateRow($data) {
    return $this->adios->db->insert_or_update_row($this->table, $data, FALSE, FALSE, $this);
  }

  public function insertRandomRow($data = [], $dictionary = []) {
    return $this->adios->db->insert_random_row($this->table, $data, $dictionary, $this);
  }

  public function updateRow($data, $id) {
    return $this->adios->db->update_row_part($this->table, $data, $id, FALSE, $this);
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

  public function pdoPrepareExecuteAndFetch(string $query, array $variables, string $keyBy = "") {
    $q = $this->pdo->prepare(str_replace(":table", $this->getFullTableSQLName(), $query));
    $q->execute($variables);

    $rows = [];
    while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
      if (empty($keyBy)) {
        $rows[] = $row;
      } else {
        $rows[$row[$keyBy]] = $row;
      }
    }

    return $rows;
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
        ".$this->lookupSqlValue()." as `input_lookup_value`
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

  public function tableCellCSVFormatter($data) {
    return $this->adios->dispatchEventToPlugins("onTableCellCSVFormatter", [
      "model" => $this,
      "data" => $data,
    ])["data"]["csv"];
  }

  public function onTableBeforeInit($tableObject) { }
  public function onTableAfterInit($tableObject) { }
  public function onTableAfterDataLoaded($tableObject) { }

  public function tableFilterColumnSqlWhere($columnName, $filterValue, $column = NULL) {
    if ($column === NULL) {
      $column = $this->columns()[$columnName];
    }

    $type = $column['type'];
    $s = explode(',', $filterValue);
    if (
      ($type == 'int' && _count($column['enum_values']))
      || in_array($type, ['varchar', 'text', 'color', 'file', 'image', 'enum', 'password', 'lookup'])
    ) {
      $w = explode(' ', $filterValue);
    } else {
      $w = $filterValue;
    }

    if ($column['virtual']) {
      if ($type == 'int' && _count($column['enum_values'])) {
        //
      } else {
        $columnName = '('.$column['sql'].')';
      }
    }

    $return = 'false';

    // trochu komplikovanejsia kontrola, ale znamena, ze vyhladavany retazec sa pouzije len ak uz nie je delitelny podla ciarok, alebo medzier
    // pripadne tato kontrola neplati ak je na zaciatku =

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
          $return = "`{$columnName}` = '".$this->adios->db->escape(trim($s))."' ";
        } else {
          $return = "(`{$columnName}` != 'Y' OR `{$columnName}` is null) ";
        }
      }

      if ('boolean' == $type) {
        if ('0' == $s) {
          $return = "(`{$columnName}` = '".$this->adios->db->escape(trim($s))."' or `{$columnName}` is null) ";
        } else {
          $return = "`{$columnName}` != '0'";
        }
      }

      if ($type == 'int' && _count($column['enum_values'])) {
        $return = " `{$columnName}_enum_value` like '%".$this->adios->db->escape(trim($s))."%'";
      } else if (in_array($type, ['varchar', 'text', 'color', 'file', 'image', 'enum', 'password'])) {
        $return = " `{$columnName}` like '%".$this->adios->db->escape(trim($s))."%'";
      } else if ($type == 'lookup') {
        if (is_numeric($s)) {
          $return = " `{$columnName}` = ".$this->adios->db->escape($s)."";
        } else {
          $return = " `{$columnName}_lookup_sql_value` like '%".$this->adios->db->escape(trim($s))."%'";
        }
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

  public function onFormBeforeInit($formObject) { }
  public function onFormAfterInit($formObject) { }

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
        $data['id'] = (int) $returnValue;
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
    } catch (\ADIOS\Core\Exceptions\FormSaveException $e) {
      return $this->adios->renderHtmlWarning($e->getMessage());
    }
  }

  public function formDelete(int $id) {
    $id = (int) $id;

    try {
      $data = $this->onBeforeDelete($id);

      $returnValue = $this->deleteRow($id);

      $returnValue = $this->adios->dispatchEventToPlugins("onModelAfterDelete", [
        "model" => $this,
        "data" => $data,
        "returnValue" => $returnValue,
      ])["returnValue"];

      $returnValue = $this->onAfterDelete($id);
      return $returnValue;
    } catch (\ADIOS\Core\Exceptions\FormDeleteException $e) {
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

  public function cardsCardHtmlFormatter($cardsObject, $data) {
    return $this->adios->dispatchEventToPlugins("onModelAfterCardsCardHtmlFormatter", [
      "model" => $this,
      "cardsObject" => $cardsObject,
      "data" => $data,
    ])["html"];
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

  public function onBeforeDelete(int $id) {
    return $this->adios->dispatchEventToPlugins("onModelBeforeDelete", [
      "model" => $this,
      "id" => $id,
    ])["id"];
  }

  public function onAfterDelete(int $id) {
    return $this->adios->dispatchEventToPlugins("onModelAfterDelete", [
      "model" => $this,
      "id" => $id,
    ])["id"];
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

      $lookupedModelColumns = $lookupedModel->columns();

      foreach ($lookupedModel->columnNames() as $lookupedColName) {
        if (!$lookupedModelColumns[$lookupedColName]['virtual'] ?? FALSE) {
          $selects[] = $lookupedModel->getFullTableSQLName().".{$lookupedColName} as {$lookupName}___LOOKUP___{$lookupedColName}";
        }
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
        $strpos = strpos($colName, "___LOOKUP___");
        if ($strpos !== FALSE) {
          $tmp1 = strtoupper(substr($colName, 0, $strpos));
          $tmp2 = substr($colName, $strpos + strlen("___LOOKUP___"));
          $row[$tmp1][$tmp2] = $colValue;
          unset($row[$colName]);
        }
      }
      $processedRows[$rowKey] = $row;
    }
    return $processedRows;
  }

  // fetchRows
  public function fetchRows($eloquentQuery, $keyBy = 'id', $processLookups = TRUE) {
    $query = $this->pdo->prepare($eloquentQuery->toSql());
    $query->execute($eloquentQuery->getBindings());

    $rows = $this->associateKey($query->fetchAll(\PDO::FETCH_ASSOC), 'id');

    if ($processLookups) {
      $rows = $this->processLookupsInQueryResult($rows);
    }

    if (!empty($eloquentQuery->pdoCrossTables)) {
      foreach ($eloquentQuery->pdoCrossTables as $crossTable) {
        list($tmpCrossTableModel, $tmpForeignKey, $tmpCrossTableResultKey) = $crossTable;

        $tmpCrossQuery = $tmpCrossTableModel->getQuery();
        $tmpCrossTableModel->addLookupsToQuery($tmpCrossQuery);
        $tmpCrossQuery->whereIn($tmpForeignKey, array_keys($rows));

        $tmpCrossTableValues = $this->fetchRows($tmpCrossQuery, 'id', FALSE);

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

  // countRowsInQuery
  public function countRowsInQuery($eloquentQuery) {
    $query = $this->pdo->prepare($eloquentQuery->toSql());
    $query->execute($eloquentQuery->getBindings());

    $rows = $query->fetchAll(\PDO::FETCH_COLUMN, 0);

    return count($rows);
  }
}