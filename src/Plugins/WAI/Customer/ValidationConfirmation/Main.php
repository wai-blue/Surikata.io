<?php

namespace Surikata\Plugins\WAI\Customer {
  class ValidationConfirmation extends \Surikata\Core\Web\Plugin {

    var $defaultUrl = "customer/{% token %}/validate-account";

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = []) {
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);

      $email = $urlVariables["email"] ?? [];
      $customerTokenAssignmentModel = new \ADIOS\Widgets\Customers\Models\CustomerTokenAssignment($this->adminPanel);

      $url = $pluginSettings["urlPattern"] ?? "";
      if (empty($url)) {
        $url = $this->defaultUrl;
      }

      $customer = $customerModel->getByEmail($email);

      $url = str_replace(
        "{% token %}",
        $customerTokenAssignmentModel->generateValidationToken(
          $customer['id'],
          $email
        ),
        $url
      );

      return $url;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $token = $this->websiteRenderer->urlVariables["token"] ?? "";
      $customerTokenAssignmentModel = new \ADIOS\Widgets\Customers\Models\CustomerTokenAssignment($this->adminPanel);

      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);

      try {
        $customerTokenInfo = $customerTokenAssignmentModel->validateToken($token);
        $customerModel->validateAccountByEmail($customerTokenInfo['CUSTOMER']['email']);
        $twigParams["status"] = "OK";
      } catch (\ADIOS\Core\InvalidToken $e) {
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
  class ValidationConfirmation extends \Surikata\Core\AdminPanel\Plugin {

    var $defaultUrl = "customer/{% token %}/validate-account";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["urlPattern"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "token" => '(.*?)'
        ]
      );

      return $siteMap;

    }

    public function getSettingsForWebsite() {
      return [
        "urlPattern" => [
          "title" => "URL pattern",
          "type" => "varchar",
          "description" => "
            Relative URL for account validation page.<br/>
            Default value: {$this->defaultUrl}
          ",
        ],
      ];
    }
  }
}