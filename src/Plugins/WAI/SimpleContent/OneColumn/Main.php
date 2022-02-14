<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class OneColumn extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class OneColumn extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-font",
        "title" => $this->translate("Formatted text with heading in one column"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "heading" => [
          "title" => "Heading",
          "type" => "varchar",
        ],
        "headingLevel" => [
          "title" => "Heading Level",
          "type" => "int",
          "enum_values" => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6],
        ],
        "content" => [
          "title" => "Content",
          "type" => "text",
          "interface" => "formatted_text",
        ],
      ];
    }
    
  }
}