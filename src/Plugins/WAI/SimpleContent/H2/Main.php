<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class H2 extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class H2 extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "heading" => [
          "title" => "Heading",
          "type" => "varchar",
        ],
      ];
    }
    
  }
}