<?php

namespace Surikata\Plugins\WAI\Customer {
  class Login extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {

      $userProfileController = new \Surikata\Core\Web\Controllers\UserProfile($this->websiteRenderer);

      if ($userProfileController->isUserLogged()) {
        $customerHomeUrl = (new \Surikata\Plugins\WAI\Customer\Home($this->websiteRenderer))->getWebPageUrl();
        $this->websiteRenderer->redirectTo($customerHomeUrl);
      }
      $twigParams = [];

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Customer {
  class Login extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "privacyTermsUrl" => [
          "title"   => "Url for privacy terms page",
          "type"    => "varchar",
        ],
        "showPrivacyTerms" => [
          "title"   => "Show privacy terms link",
          "type"    => "boolean",
        ]
      ];
    }

  }
}