<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerTokenAssignment extends \ADIOS\Core\Model {
  const TOKEN_TYPE_ACCOUNT_VALIDATION = 15901;
  const TOKEN_TYPE_ACCOUNT_CONFIRMATION = 15902;
  const TOKEN_TYPE_FORGOT_PASSWORD = 4844;

  var $sqlName = "customers_tokens";
  var $urlBase = "Customer/{{ id_customer }}/Tokens";
  var $tableTitle = "Tokens";

  public static $init = TRUE;

  public function init() {
    if (self::$init === TRUE) {
      self::$init = FALSE;
      parent::init();
      $tokenModel = $this->adios->getModel("Core/Models/Token");

      $tokenModel->registerTokenType(self::TOKEN_TYPE_ACCOUNT_VALIDATION);
      $tokenModel->registerTokenType(self::TOKEN_TYPE_ACCOUNT_CONFIRMATION);
      $tokenModel->registerTokenType(self::TOKEN_TYPE_FORGOT_PASSWORD);
    }
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "model" => "Widgets/Customers/Models/Customer",
        "title" => "Customer",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_token" => [
        "type" => "lookup",
        "model" => "Core/Models/Token",
        "title" => "Token",
        "required" => TRUE,
        "show_column" => TRUE,
      ]
    ]);
  }

  public function getCustomerTokenInfo($token) {
    $customerTokenQuery = $this->getQuery();

    $this->addLookupsToQuery($customerTokenQuery, ['id_customer' => 'CUSTOMER']);
    $this->addLookupsToQuery($customerTokenQuery, ['id_token' => 'TOKEN']);
    
    $customerTokenQuery->where('token', $token);
    
    return reset($this->fetchQueryAsArray($customerTokenQuery));
  }

  public function generateToken($idCustomer, $tokenSalt, $tokenType) {
    $tokenModel = $this->adios->getModel("Core/Models/Token");
    $token = $tokenModel->generateToken($tokenSalt, $tokenType);

    $this->insertRow([
      "id_customer" => $idCustomer,
      "id_token" => $token['id'],
    ]);

    return $token['token'];
  }

  public function generateValidationToken($idCustomer, $tokenSalt) {
    return $this->generateToken(
      $idCustomer,
      $tokenSalt,
      self::TOKEN_TYPE_ACCOUNT_VALIDATION
    );
  }

  public function generateConfirmationToken($idCustomer, $tokenSalt) {
    return $this->generateToken(
      $idCustomer,
      $tokenSalt,
      self::TOKEN_TYPE_ACCOUNT_CONFIRMATION
    );
  }

  public function generateForgotPasswordToken($idCustomer, $tokenSalt) {
    return $this->generateToken(
      $idCustomer,
      $tokenSalt,
      self::TOKEN_TYPE_FORGOT_PASSWORD
    );
  }

  public function validateToken($token, $deleteAfterValidation = TRUE) {
    $tokenModel = $this->adios->getModel("Core/Models/Token");
    $tokenData = $tokenModel->validateToken($token);

    $customerTokenInfo = $this->getCustomerTokenInfo($token);

    if ($deleteAfterValidation) {
      $this->where('id_token', $tokenData['id'])->delete();
      $tokenModel->deleteToken($tokenData['id']);
    }
   
    return $customerTokenInfo;
  }

}