<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Models;

/**
 * Model for storing list of user roles. Stored in 'roles' SQL table.
 *
 * @package DefaultModels
 */
class UserRole extends \ADIOS\Core\Model {
  var $sqlName = "";
  var $lookupSqlValue = "{%TABLE%}.name";
  
  public function __construct(&$adios) {
    $this->sqlName = "{$adios->config['system_table_prefix']}_roles";
    parent::__construct($adios);
  }

  public function columns(array $columns = []) {
    return parent::columns([
      'name' => array('type' => 'varchar', 'title' => 'Názov'),
    ]);
  }
}