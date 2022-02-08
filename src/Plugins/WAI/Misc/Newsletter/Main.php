<?php

namespace Surikata\Plugins\WAI\Misc {

  use ADIOS\Widgets\CRM\Exceptions\AlreadyRegisteredForNewsletter;
  use ADIOS\Widgets\CRM\Exceptions\EmailIsInvalid;

  class Newsletter extends \Surikata\Core\Web\Plugin {

    public function renderJSON() {
      $returnArray = array();

      $domain = $this->websiteRenderer->twigParams["domain"] ?? "";
      // REVIEW: Premenovanie action na nieco ine + pozriet na inych miestach
      $action = $this->websiteRenderer->urlVariables['action'] ?? "";
      $email = $this->websiteRenderer->urlVariables['email'] ?? "";

      switch ($action) {
        case "subscribe": // REVIEW: Zjednotenia nazvu subscribe a register
          try {
            $newsletterModel = new \ADIOS\Widgets\CRM\Models\Newsletter($this->websiteRenderer->adminPanel);
            $newsletterModel->registerForNewsletter($email, $domain);
            $returnArray["status"] = "OK";
          } catch (\Exception $e) {
            $returnArray["status"] = "FAIL";
            $returnArray["exception"] = get_class($e);
            $returnArray["error"] = $e->getMessage();
          }
        break;
      }

      return $returnArray;

    }

    public function getTwigParams($pluginSettings) {
      return $pluginSettings;
    }
  }

}

namespace ADIOS\Plugins\WAI\Misc {
  class Newsletter extends \Surikata\Core\AdminPanel\Plugin {

  }
}