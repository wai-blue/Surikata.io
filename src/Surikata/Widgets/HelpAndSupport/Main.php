<?php

namespace ADIOS\Widgets;

class HelpAndSupport extends \ADIOS\Core\Widget {
  public function init() {
    $this->adios->config['desktop']['sidebarItems']['Help'] = [
      "fa_icon" => "fas fa-question",
      "title" => $this->translate("Help & Support"),
      "onclick" => "desktop_update('HelpAndSupport/Main');",
      "sub" => [
        [
          "title" => $this->translate("User guide"),
          "onclick" => "window.open('https://www.surikata.io/documentation/user-guide')",
        ],
        [
          "title" => $this->translate("Contact us"),
          "onclick" => "desktop_update('HelpAndSupport/ContactForm');",
        ],
      ],
    ];
  }

}