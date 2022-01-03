<?php

namespace ADIOS\Widgets;

class HelpAndSupport extends \ADIOS\Core\Widget {
  public function getUserGuideUrl() {
    $userGuideUrls = [
      "en" => "https://www.surikata.io/documentation/user-guide",
      "sk" => "https://www.surikata.io/sk/pouzivatelska-prirucka",
      "cz" => "https://www.surikata.io/sk/pouzivatelska-prirucka",
    ];

    return $userGuideUrls[$this->adios->config["language"]] ?? $userGuideUrls["en"];
  }

  public function init() {
    $this->adios->config['desktop']['sidebarItems']['Help'] = [
      "fa_icon" => "fas fa-question",
      "title" => $this->translate("Help & Support"),
      "onclick" => "desktop_update('HelpAndSupport/Main');",
      "sub" => [
        [
          "title" => $this->translate("User guide"),
          "onclick" => "window.open('".$this->getUserGuideUrl()."')",
        ],
        [
          "title" => $this->translate("Programmer's guide"),
          "onclick" => "window.open('https://www.surikata.io/documentation')",
        ],
        [
          "title" => $this->translate("Contact us"),
          "onclick" => "window_render('HelpAndSupport/ContactForm');",
        ],
      ],
    ];
  }

}