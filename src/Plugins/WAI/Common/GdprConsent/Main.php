<?php

namespace Surikata\Plugins\WAI\Common {

  class GdprConsent extends \Surikata\Core\Web\Plugin {

    public function renderJSON() {
      $returnArray = [];

      $returnArray["content"] = $this->websiteRenderer->twig->render(
        "{$this->websiteRenderer->twigTemplatesSubDir}/Plugins/WAI/Common/GdprConsent.twig", []);

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Common {
  class GdprConsent extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("GDPR and privacy content popup"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "showContactAddress" => [
          "title" => "Show contact address",
          "type" => "boolean"
        ],
        "showContactEmail" => [
          "title" => "Show contact email",
          "type" => "boolean"
        ],
        "showContactPhoneNumber" => [
          "title" => "Show contact phone number",
          "type" => "boolean"
        ],
        "contactTitle" => [
          "title" => "Contact title",
          "type" => "varchar"
        ],
        "mainMenuId" => [
          "title" => "Main menu",
          "type" => "int",
          "enum_values" => (new \ADIOS\Widgets\Website\Models\WebMenu($this->adios))
            ->getEnumValues()
          ,
        ],
        "mainMenuTitle" => [
          "title" => "Main menu title",
          "type" => "varchar",
        ],
        "showMainMenu" => [
          "title" => "Show main menu",
          "type" => "boolean",
        ],
        "secondaryMenuId" => [
          "title" => "Secondary menu",
          "type" => "int",
          "enum_values" => (new \ADIOS\Widgets\Website\Models\WebMenu($this->adios))
            ->getEnumValues()
          ,
        ],
        "secondaryMenuTitle" => [
          "title" => "Secondary menu title",
          "type" => "varchar",
        ],
        "showSecondaryMenu" => [
          "title" => "Show secondary menu",
          "type" => "boolean",
        ],
        "showNewsletter" => [
          "title" => "Zobraziť pole pre zaradenie do newsletteru",
          "type" => "boolean",
        ],
        "showPayments" => [
          "title" => "Show payments methods",
          "type" => "boolean"
        ],
        "showSocialMedia" => [
          "title" => "Show social media links",
          "type" => "boolean"
        ]
      ];
    }
    
  }
}