<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class HTML extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class HTML extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-font",
        "title" => $this->translate("Simple content - Formatted (HTML) text"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "content" => [
          "title" => "Content",
          "type" => "text",
          "interface" => "formatted_text",
        ],
      ];
    }
  }
}