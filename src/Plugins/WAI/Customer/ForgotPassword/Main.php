<?php

namespace Surikata\Plugins\WAI\Customer {
  class ForgotPassword extends \Surikata\Core\Web\Plugin {

    var $defaultForgotPasswordUrl = "password/reset/{% token %}";
    // REVIEW-PH: Toto treba spravit inym sposobom nie natvrdo nakodene
    var $formForgotPasswordUrl = [
      1 => "forgot-password/{% token %}",
      2 => "obnovit-heslo/{% token %}",
      3 => "obnovit-heslo/{% token %}",
    ];

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {
      $email = $urlVariables["email"] ?? "";
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
      $customerTokenAssignmentModel = new \ADIOS\Widgets\Customers\Models\CustomerTokenAssignment($this->adminPanel);

      $url = $pluginSettings["urlPattern"] ?? "";
      if (empty($url)) {
        if ($email != "") {
          $url = $this->defaultForgotPasswordUrl;
        } else {
          $url = $this->formForgotPasswordUrl[$languageIndex];
        }
      }

      $customer = $customerModel->getByEmail($email);

      $token = $email 
        ? "" .
          $customerTokenAssignmentModel->generateForgotPasswordToken(
            $customer['id'],
            $email
          ) 
        : ""
      ;

      $url = str_replace("{% token %}", $token, $url);

      return $url;
    }

    public function renderJSON() {
      $email = $this->websiteRenderer->urlVariables['email'] ?? "";
      $returnArray = [];

      try {
        $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
        $customerModel->forgotPassword($email);
        $returnArray["status"] = "OK";
      } catch (
          \ADIOS\Widgets\Customers\Exceptions\EmailIsInvalid 
          | \ADIOS\Widgets\Customers\Exceptions\EmailIsEmpty
          | \ADIOS\Widgets\Customers\Exceptions\UnknownAccount
          | \ADIOS\Widgets\Customers\Exceptions\AccountIsNotValidated
        $e) {
        $returnArray["status"] = "FAIL";
        $returnArray["exception"] = get_class($e);
        $returnArray["error"] = $e->getMessage();
      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $token = $this->websiteRenderer->urlVariables['token'] ?? "";
      $customerTokenAssignmentModel = new \ADIOS\Widgets\Customers\Models\CustomerTokenAssignment($this->adminPanel);

      try {
        $tokenData = $customerTokenAssignmentModel->validateToken($token, FALSE);

        $twigParams["status"] = "OK";
      } catch (\ADIOS\Core\Exceptions\InvalidToken $e) {
        $twigParams["status"] = "FAIL";
        $twigParams["error"] = "Invalid token: ".$e->getMessage();
      } catch (\Exception $e) {
        $twigParams["status"] = "FAIL";
        $twigParams["error"] = $e->getMessage();
      }

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Customer {
  class ForgotPassword extends \Surikata\Core\AdminPanel\Plugin {

    var $defaultForgotPasswordUrl = "password/reset/{% token %}";

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Reset password"),
      ];
    }

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["defaultForgotPasswordUrl"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultForgotPasswordUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "token" => "(.*?)"
        ]
      );

      return $siteMap;
    }
  }
}