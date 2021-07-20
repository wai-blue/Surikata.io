<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Models;

class User extends \ADIOS\Core\Model {
  var $sqlName = "";
  var $urlBase = "Pouzivatelia";
  var $tableTitle = "Users";
  var $lookupSqlValue = "concat({%TABLE%}.name, ' ', {%TABLE%}.surname)";

  public function __construct($adios) {
    $this->sqlName = "{$adios->config['system_table_prefix']}_users";
    parent::__construct($adios);
  }

  public function columns(array $columns = []) {
    return parent::columns([
      'name' => ['type' => 'varchar', 'title' => 'Given name', 'show_column' => true],
      'surname' => ['type' => 'varchar', 'title' => 'Family name', 'show_column' => true],
      'login' => ['type' => 'varchar', 'title' => 'Login'],
      'password' => ['type' => 'password', 'title' => 'Password'],
      'email' => ['type' => 'varchar', 'title' => 'Email'],
      'id_role' => ['type' => 'lookup', 'title' => 'Role', 'model' => "Core/Models/UserRole", 'show_column' => true, 'input_style' => 'select'],
      'photo' => ['type' => 'image', 'title' => 'Photo', 'only_upload' => 'yes', 'subdir' => 'users/'],
      'active' => ['type' => 'boolean', 'title' => 'Active', 'show_column' => true],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^MojProfil$/' => [
        "action" => "UI/Form",
        "params" => [
          "model" => "Core/Models/User",
          "id" => $this->adios->userProfile['id'],
        ]
      ],
    ]);
  }

  public function getById($id) {
    $id = (int) $id;
    $user = self::find($id);
    return ($user === NULL ? [] : $user->toArray());
  }

}