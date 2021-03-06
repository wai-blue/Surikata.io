<?php

namespace Surikata\Core\Web\Controllers;

use ADIOS\Widgets\Customers\Models\CustomerUID;

class UserProfile extends \Surikata\Core\Web\Controller {

  var $loginFailed = FALSE;
  var $changePasswordError = FALSE;
  var $changeNameError = FALSE;

  // getters
  public function isUserLogged() {
    return !empty($_SESSION['idUserLogged']) && is_numeric($_SESSION['idUserLogged']) && $_SESSION['idUserLogged'] > 0;
  }

  public function getUserLoggedId() {
    return (int) ($_SESSION['idUserLogged'] ?? 0);
  }

  public function getUserLogged() {
    
    if (empty($_SESSION['userProfile'])) {
      $idUserLogged = $this->getUserLoggedId();
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
      $_SESSION['userProfile'] = $customerModel->getById($idUserLogged);
    }
    return $_SESSION['userProfile'];
  }

  public function reloadUserProfile() {
    unset($_SESSION['userProfile']);
    $this->websiteRenderer->userLogged = $this->getUserLogged();
  }

  // setters
  public function setUserLogged($idUser, $userProfile) {
    if ($idUser === NULL) {
      unset($_SESSION['idUserLogged']);
    } else {
      $_SESSION['idUserLogged'] = $idUser;
    }

    if ($userProfile === NULL) {
      unset($_SESSION['userProfile']);
    } else {
      $_SESSION['userProfile'] = $userProfile;
    }
  }

  public function setCookieConsent($consentJson) {
    $customerUid = $this->websiteRenderer->getCustomerUID();
    $customerUidModel = new CustomerUID($this->adminPanel);
    $customerUidModel->setCookieConsent($customerUid, $consentJson);
  }

  // preRender
  public function preRender() {
    $this->websiteRenderer->userLogged = NULL;

    // Request to set cookie consent
    if (isset($_POST['setCookieConsent'])) {
      $consent = @$_COOKIE['srkt-cookie-consent-settings'] ?? "{'necessary':true}";

      $this->setCookieConsent($consent);
    }

    // Request to change customer password
    if (isset($_POST['changeBasicInformation']) && $_POST['changeBasicInformation'] == "1") {
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
      $given_name = $_POST["given_name"] ?? "";
      $family_name = $_POST["family_name"] ?? "";

      // REVIEW: $vsetkyPremenneVCamelCase
      $company_name = $_POST["company_name"] ?? "";
      $company_id = $_POST["company_id"] ?? "";
      $company_tax_id = $_POST["company_tax_id"] ?? "";
      $company_vat_id = $_POST["company_vat_id"] ?? "";
      // REVIEW: $email sa nikde nepouziva, chyba/zamer/zbytocne?
      $email = $_POST["email"] ?? "";

      $address = [];
      $address["inv_given_name"] = $_POST["inv_given_name"] ?? "";
      $address["inv_family_name"] = $_POST["inv_family_name"] ?? "";
      $address["inv_company_name"] = $_POST["inv_company_name"] ?? "";
      $address["inv_street_1"] = $_POST["inv_street_1"] ?? "";
      $address["inv_street_2"] = $_POST["inv_street_2"] ?? "";
      $address["inv_zip"] = $_POST["inv_zip"] ?? "";
      $address["inv_city"] = $_POST["inv_city"] ?? "";

      try {
        $customerModel->changeName(
          $this->getUserLogged(),
          $given_name,
          $family_name
        );

        $customerModel->changeCompanyInfo(
          $this->getUserLogged(),
          $company_name,
          $company_id,
          $company_tax_id,
          $company_vat_id
        );

        $customerModel->changeBillingInfo(
          $this->getUserLogged(),
          $address
        );
        $this->reloadUserProfile();
        $this->changeNameError = "";
      } catch(\ADIOS\Widgets\Customers\Exceptions\UnknownError $e) {

        $this->changeNameError = $e->getMessage();

      }

    }

    // Request to change customer password
    if ($_POST['changePassword'] ?? "0" == "1") { 
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
      $currentPassword = $_POST["current_password"] ?? "";
      $password1 = $_POST['password_1'] ?? "";
      $password2 = $_POST['password_2'] ?? "";

      try {
        $customerModel->changePassword(
          $this->getUserLogged(),
          $currentPassword,
          $password1,
          $password2
        );
        $this->changePasswordError = "";
      } catch(
        \ADIOS\Widgets\Customers\Exceptions\InvalidPassword
        | \ADIOS\Widgets\Customers\Exceptions\NewPasswordsDoNotMatch
        | \ADIOS\Widgets\Customers\Exceptions\NewPasswordIsEmpty
        | \ADIOS\Widgets\Customers\Exceptions\UnknownError
        | \ADIOS\Widgets\Customers\Exceptions\InvalidPasswordLength
        $e
      ) {
        $this->changePasswordError = $e->getMessage();
      }

    }

    if ($_POST['forgotPassword'] ?? "0" == "1") { 
      $token = $this->websiteRenderer->urlVariables['token'] ?? "";
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
      $customerTokenAssignmentModel = new \ADIOS\Widgets\Customers\Models\CustomerTokenAssignment($this->adminPanel);
      
      $password = $_POST['password'] ?? "";

      try {
        $tokenData = $customerTokenAssignmentModel->validateToken($token, FALSE);

        if (empty($tokenData["CUSTOMER"]['email'])) {
          throw new \ADIOS\Widgets\Customers\Exceptions\UnknownAccount();
        }

        $customerInfo = $customerModel->getByEmail($tokenData["CUSTOMER"]['email']);
        $customerModel->changeForgotPassword(
          $customerInfo,
          $password
        );

        $customerTokenAssignmentModel->validateToken($token);
        $this->setUserLogged($customerInfo['id'], $customerInfo);

        $customerHomeUrl = (new \Surikata\Plugins\WAI\Customer\Home($this->websiteRenderer))->getWebPageUrl();
        $this->websiteRenderer->redirectTo($customerHomeUrl);
      } catch(
        \ADIOS\Widgets\Customers\Exceptions\InvalidPassword
        | \ADIOS\Widgets\Customers\Exceptions\NewPasswordsDoNotMatch
        | \ADIOS\Widgets\Customers\Exceptions\NewPasswordIsEmpty
        | \ADIOS\Widgets\Customers\Exceptions\UnknownError
        | \ADIOS\Widgets\Customers\Exceptions\InvalidPasswordLength
        $e
      ) {
        $this->changePasswordError = $e->getMessage();
      }

    }

    // prihlasim pouzivatela, ak je to pozadovane
    if ($_POST['doLogin'] ?? "0" == "1") {
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);

      $this->websiteRenderer->userLogged = $customerModel->authenticate($_POST['loginEmail'], $_POST['loginPassword']);

      if ($this->websiteRenderer->userLogged !== NULL) {
        $this->setUserLogged($this->websiteRenderer->userLogged['id'], $this->websiteRenderer->userLogged);
        // $this->websiteRenderer->redirectTo("moj-ucet");
      } else {
        $this->setUserLogged(NULL, NULL);
        $this->loginFailed = TRUE;
      }
    } else {
      $this->websiteRenderer->userLogged = $this->getUserLogged();
    }

    // odhlasim pouzivatela, ak je to pozadovane
    if ($_REQUEST["doLogout"] ?? "0" == "1") {
      $this->setUserLogged(NULL, NULL);
    }

    $this->websiteRenderer->setTwigParams([
      "user" => [
        "isLogged" => $this->isUserLogged(),
        "profile" => $this->websiteRenderer->userLogged,
        "loginFailed" => $this->loginFailed,
        "changePasswordError" => $this->changePasswordError,
        "changeNameError" => $this->changeNameError,
      ],
    ]);
  }

}
