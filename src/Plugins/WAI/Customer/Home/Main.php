<?php

namespace Surikata\Plugins\WAI\Customer {

  use Surikata\Core\Web\Controllers\UserProfile;

  class Home extends \Surikata\Core\Web\Plugin {

    public function removeAddress() {
      $idCustomer = (int) $this->websiteRenderer->userLogged['id'];
      $idAddress = (int) $this->websiteRenderer->urlVariables['idAddress'] ?? 0;

      $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($this->adminPanel);
      $customerAddressModel->removeAddress($idCustomer, $idAddress);

      return TRUE;
    }

    public function editAddress($idAddress) {
      $idCustomer = (int) $this->websiteRenderer->userLogged['id'];
      $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($this->adminPanel);
      $data = $this->websiteRenderer->urlVariables;

      if ($idAddress > 0) {
        return $customerAddressModel->saveAddress($idCustomer, $data);
      }
      else {
        return $customerAddressModel->saveAddress($idCustomer, $data);
      }
    }

    public function getAddress($idAddress) {
      $idCustomer = (int) $this->websiteRenderer->userLogged['id'];

      $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($this->adminPanel);
      $address = $customerAddressModel->getById($idAddress);

      if ($address["id_customer"] != $idCustomer) {
        return [];
      }

      return $address;
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
        case 'editAddressModal':
          try {
            $idAddress = (int)$this->websiteRenderer->urlVariables['idAddress'] ?? 0;
            $params = [];
            if ($idAddress !== 0) {
              $params["address"] = $this->getAddress($idAddress);
            }
            $returnArray["status"] = "OK";
            $returnArray["addressModalContent"] =
              $this->websiteRenderer->twig->render(
                "{$this->websiteRenderer->twigTemplatesSubDir}/Plugins/WAI/Customer/Modal/EditAddress.twig",
                $params
              )
            ;
          }
          catch (\Exception $e) {
            $returnArray["status"] = "FAIL";
            $returnArray["exception"] = get_class($e);
            $returnArray["error"] = $e->getMessage();
          }
        break;
        case "editAddress":
          try {
            $idAddress = (int)$this->websiteRenderer->urlVariables['idAddress'] ?? 0;
            $returnArray["status"] = "OK";
            $returnArray["id_address"] = $this->editAddress($idAddress);
          }
          catch (\Exception $e) {
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
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Customer's home page"),
      ];
    }

  }
}