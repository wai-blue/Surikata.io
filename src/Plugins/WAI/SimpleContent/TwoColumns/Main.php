<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class TwoColumns extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class TwoColumns extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "column1Content" => [
          "title" => "Column 1 - content",
          "type" => "text",
          "interface" => "formatted_text",
        ],
        "column1Width" => [
          "title" => "Column 1 - width",
          "type" => "int",
          "enum_values" => [
            1 => "1/12",
            2 => "2/12",
            4 => "4/12",
            6 => "6/12",
            8 => "8/12",
            10 => "12/12",
            12 => "12/12",
          ],
        ],
        "column1CSSClasses" => [
          "title" => "Column 1 - additional CSS classes",
          "type" => "varchar",
        ],
        "column2Content" => [
          "title" => "Content - column 2",
          "type" => "text",
          "interface" => "formatted_text",
        ],
        "column2Width" => [
          "title" => "Column 2 - width",
          "type" => "int",
          "enum_values" => [
            1 => "1/12",
            2 => "2/12",
            4 => "4/12",
            6 => "6/12",
            8 => "8/12",
            10 => "12/12",
            12 => "12/12",
          ],
        ],
        "column2CSSClasses" => [
          "title" => "Column 2 - additional CSS classes",
          "type" => "varchar",
        ],
      ];
    }
    
  }
}