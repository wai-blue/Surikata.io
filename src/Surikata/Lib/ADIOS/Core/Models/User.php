<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Models;

/**
 * Model for storing user profiles. Stored in 'users' SQL table.
 *
 * @package DefaultModels
 */
class User extends \ADIOS\Core\Model {
  var $sqlName = "";
  var $urlBase = "Users";
  var $lookupSqlValue = "concat({%TABLE%}.name, ' ', {%TABLE%}.surname)";

  public function __construct($adiosOrAttributes) {
    $this->sqlName = "{$adiosOrAttributes->config['system_table_prefix']}_users";
    parent::__construct($adiosOrAttributes);

    if (is_object($adiosOrAttributes)) {
      $this->tableTitle = $this->translate("Users");
    }
  }

  public function columns(array $columns = []) {
    return parent::columns([
      'name' => ['type' => 'varchar', 'title' => $this->translate('Given name'), 'show_column' => true],
      'surname' => ['type' => 'varchar', 'title' => $this->translate('Family name'), 'show_column' => true],
      'login' => ['type' => 'varchar', 'title' => $this->translate('Login')],
      'password' => ['type' => 'password', 'title' => $this->translate('Password')],
      'email' => ['type' => 'varchar', 'title' => $this->translate('Email')],
      'id_role' => ['type' => 'lookup', 'title' => $this->translate('Role'), 'model' => "Core/Models/UserRole", 'show_column' => true, 'input_style' => 'select'],
      'photo' => ['type' => 'image', 'title' => $this->translate('Photo'), 'only_upload' => 'yes', 'subdir' => 'users/', "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),],
      'active' => ['type' => 'boolean', 'title' => $this->translate('Active'), 'show_column' => true],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^MyProfile$/' => [
        "action" => "UI/Form",
        "params" => [
          "model" => "Core/Models/User",
          "myProfileView" => TRUE,
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

  public function formParams($data, $params) {
    if ($params["myProfileView"]) {
      $params['show_delete_button'] = FALSE;
      $params['template'] = [
        "columns" => [
          [
            "rows" => [
              "name",
              "surname",
              "password",
              "email",
            ],
          ],
        ],
      ];
    }
    
    return $params;
  }

}