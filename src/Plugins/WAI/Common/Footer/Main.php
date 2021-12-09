<?php

namespace Surikata\Plugins\WAI\Common {

  class Footer extends \Surikata\Core\Web\Plugin {

    public function getMenuItems($flatMenuItems) {
      $menuItems = [];

      foreach ($flatMenuItems as $flatItem) {
        if ($flatItem['id_parent'] == 0) {
          $children = [];
          if ($flatItem["expand_product_categories"] == 1) {
            foreach ($flatMenuItems as $flatItemSub) {
              if ($flatItemSub['id_parent'] == $flatItem['id']) {
                $children[] = [
                  "url" => $flatItemSub['url'],
                  "text" => $flatItemSub['title'],
                ];
              }
            }
          }

          $menuItems[] = [
            "text" => $flatItem['title'],
            "url" => $flatItem['url'],
            "children" => $children,
          ];
        }
      }

      return $menuItems;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $webMenuItemModel = new \ADIOS\Widgets\Website\Models\WebMenuItem($this->adminPanel);

      $mainMenuId = (int) $pluginSettings['mainMenuId'] ?? 0;
      $secondaryMenuId = (int) $pluginSettings['secondaryMenuId'] ?? 0;

      $flatMainMenuItems = $webMenuItemModel->getByIdMenu($mainMenuId);
      $flatSecondaryMenuItems = $webMenuItemModel->getByIdMenu($secondaryMenuId);

      $twigParams['mainMenuItems'] = $this->getMenuItems($flatMainMenuItems);
      $twigParams['secondaryMenuItems'] = $this->getMenuItems($flatSecondaryMenuItems);

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Common {
  class Footer extends \Surikata\Core\AdminPanel\Plugin {

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
        "Newsletter" => [
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