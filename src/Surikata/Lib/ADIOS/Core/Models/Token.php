<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Models;

/**
 * Model for storing various validation tokens. Stored in 'tokens' SQL table.
 *
 * @package DefaultModels
 */
class Token extends \ADIOS\Core\Model {
  var $sqlName = "";
  var $lookupSqlValue = "{%TABLE%}.token";

  public $tokenTypes = [];

  public function __construct(&$adios) {
    $this->sqlName = "{$adios->config['system_table_prefix']}_tokens";
    parent::__construct($adios);
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "type" => [
        "type" => "int",
        "title" => "Token type",
      ],
      "valid_to" => [
        "type" => "datetime",
        "title" => "Expiration date"
      ],
      "token" => [
        "type" => "varchar",
        "title" => "Token"
      ]
    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "uid" => [
        "type" => "index",
        "columns" => ["token"],
      ],
    ]);
  }

  public function registerTokenType($type) {
    if (!in_array($type, $this->tokenTypes)) {
      $this->tokenTypes[] = $type;
    } else {
      throw new \ADIOS\Core\Exceptions\GeneralException("Duplicate token type: {$type}");
    }
  }

  public function generateToken($tokenSalt, $tokenType, $validTo = NULL) {
    $token = uniqid()."-".md5($tokenSalt);

    if (!in_array($tokenType, $this->tokenTypes)) {
      throw new \ADIOS\Core\Exceptions\GeneralException("Unknown token type: {$tokenType}");
    }

    if ($validTo === NULL) {
      $validTo = date("Y-m-d H:i:s", strtotime("+ 3 day", time()));
    }

    if (strtotime($validTo) < time()) {
      throw new \ADIOS\Core\Exceptions\GeneralException("Token validity can not be in the past.");
    }

    $tokenId = $this->insertRow([
      "type" => $tokenType,
      "valid_to" => $validTo,
      "token" => $token,
    ]);

    return ["id" => $tokenId, "token" => $token];
  }

  public function validateToken($token) {
    $tokenQuery = $this->getQuery('*');
    $tokenQuery
      ->where("token", "=", $token)
      ->whereDate("valid_to", ">=", date("Y-m-d H:i:s"))
    ;

    $tokenData = reset($this->fetchRows($tokenQuery));

    if (!is_array($tokenData)) {
      throw new \ADIOS\Core\Exceptions\InvalidToken($token);
    }

    return $tokenData;
  }

  public function deleteToken($tokenId) {
    $this->where('id', $tokenId)->delete();
  }
}