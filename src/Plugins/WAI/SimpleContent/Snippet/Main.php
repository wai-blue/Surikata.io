<?php

namespace Surikata\Plugins\WAI\SimpleContent {
  class Snippet extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\SimpleContent {
  class Snippet extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Custom HTML snippet"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "snippetName" => [
          "title" => "Snippet name",
          "type" => "varchar",
        ],
      ];
    }
  }
}