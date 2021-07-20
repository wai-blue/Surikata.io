<?php

namespace Surikata\Plugins\WAI\Customer {
  class Home extends \Surikata\Core\Web\Plugin {

    public function removeAddress() {
      $idCustomer = (int) $this->websiteRenderer->userLogged['id'];
      $idAddress = (int) $this->websiteRenderer->urlVariables['idAddress'] ?? 0;

      $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($this->adminPanel);
      $customerAddressModel->removeAddress($idCustomer, $idAddress);

      return TRUE;
    }

    public function renderJSON() {
      $customerAction = $this->websiteRenderer->urlVariables['customerAction'] ?? "";
      $returnArray = [];

      switch ($customerAction) {
        case 'removeAddress':
          try {
            $this->removeAddress();

            $userProfileController = new \Surikata\Core\Web\Controllers\UserProfile($this->websiteRenderer);
            $userProfileController->reloadUserProfile();

            $returnArray["status"] = "OK";
          } catch (\ADIOS\Widgets\Customers\Exceptions\RemoveAddressUnknownError $e) {
            $returnArray["status"] = "FAIL";
            $returnArray["exception"] = get_class($e);
            $returnArray["error"] = $e->getMessage();
          }
        break;
      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {

      $userProfileController = new \Surikata\Core\Web\Controllers\UserProfile($this->websiteRenderer);
      $userProfileController->reloadUserProfile();

      if (!$userProfileController->isUserLogged()) {
        $loginUrl = (new \Surikata\Plugins\WAI\Customer\Login($this->websiteRenderer))->getWebPageUrl();
        $this->websiteRenderer->redirectTo($loginUrl);
      }
      $twigParams = [];

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Customer {
  class Home extends \Surikata\Core\AdminPanel\Plugin {
  }
}