<?php

namespace Surikata\Plugins\WAI\Customer {
  class Registration extends \Surikata\Core\Web\Plugin {

    public function renderJSON() {
      if ($this->websiteRenderer->urlVariables['createAccount'] ?? FALSE) {
        $returnArray = [];

        try {
          $customerUID = $this->websiteRenderer->getCustomerUID();
          foreach ($this->websiteRenderer->urlVariables as $key => $field) {
            $this->websiteRenderer->urlVariables[$key] = hsc($field);
          }
          $email = $this->websiteRenderer->urlVariables['email'] ?? "";

          $idCustomer = $this->adminPanel
            ->getModel("Widgets/Customers/Models/Customer")
            ->createAccount(
              $customerUID,
              $email,
              $this->websiteRenderer->urlVariables,
              FALSE // saveAddress
            )
          ;

          $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel);
          $createdAccount = $customerModel->getById($idCustomer);

          $returnArray["idCustomer"] = $idCustomer;
          $returnArray["status"] = "OK";
          $returnArray["createdAccount"] = $createdAccount;
          $returnArray["registrationConfirmationUrl"] = (new \Surikata\Plugins\WAI\Customer\RegistrationConfirmation($this->websiteRenderer))
            ->getWebPageUrl(["email" => $createdAccount['email']])
          ;
        } catch (
          \ADIOS\Widgets\Customers\Exceptions\EmailIsEmpty
          | \ADIOS\Widgets\Customers\Exceptions\EmailIsInvalid
          | \ADIOS\Widgets\Customers\Exceptions\AccountAlreadyExists
          | \ADIOS\Widgets\Customers\Exceptions\CreateAccountUnknownError
          | \ADIOS\Widgets\Customers\Exceptions\EmptyRequiredFields
          $e
        ) {
          $returnArray["status"] = "FAIL";
          $returnArray["exception"] = get_class($e);
          $returnArray["error"] = $e->getMessage();
        }

        return $returnArray;

      } else {
        return parent::render();
      }

    }

  }
}

namespace ADIOS\Plugins\WAI\Customer {
  class Registration extends \Surikata\Core\AdminPanel\Plugin {

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