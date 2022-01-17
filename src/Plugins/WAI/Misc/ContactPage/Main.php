<?php

namespace Surikata\Plugins\WAI\Misc {
  class ContactPage extends \Surikata\Core\Web\Plugin {

  }
}

namespace ADIOS\Plugins\WAI\Misc {
  class ContactPage extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "enableContactForm" => [
          "title" => $this->translate("Enable contact form"),
          "type" => "boolean",
          "description" => $this->translate("Enable the contact form thanks to whom customers can contact you")
        ],
        "map" => [
          "title" => $this->translate("Company map location"),
          "type" => "text",
          "description" => $this->translate("Copy your company location as html code from maps.") .
          "</br>" . $this->translate("Example how copy Google maps html: ") . "https://extension.umaine.edu/plugged-in/technology-marketing-communications/web/tips-for-web-managers/embed-map/"
        ]
      ];
    }

  }
}