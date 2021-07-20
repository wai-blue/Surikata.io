<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class LegalDisclaimer extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class LegalDisclaimer extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "displayedDisclaimer" => [
          "title" => "Displayed disclaimer",
          "type" => "varchar",
          "enum_values" => [
            "" => "Choose disclaimer to display",
            "GT" => "General Terms",
            "PP" => "Privacy Policy",
            "RP" => "Return Policy",
          ]
        ],
      ];
    }
    
  }
}