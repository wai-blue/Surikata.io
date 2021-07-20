<?php

namespace Surikata\Plugins\WAI\Customer {
  class RegistrationConfirmation extends \Surikata\Core\Web\Plugin {

    var $defaultUrl = "registration/{% token %}/confirm";

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
        $customerTokenAssignmentModel->generateConfirmationToken(
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
        $twigParams["accountInfo"] = $customerTokenInfo;
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
  class RegistrationConfirmation extends \Surikata\Core\AdminPanel\Plugin {

    var $defaultUrl = "registration/{% token %}/confirm";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["urlPattern"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "token" => '(.*?)',
        ]
      );

      return $siteMap;
    }

    public function getSettingsForWebsite() {
      return [
        "urlPattern" => [
          "title" => "Register confirmation page URL",
          "type" => "varchar",
          "description" => "
            Relative URL for register confirmation page.<br/>
            Default value: {$this->defaultUrl}
          ",
        ],
      ];
    }
  }
}