<?php

namespace MyOnlineStore;

class Web extends \Surikata\Core\Web\Loader {
  /* Uncomment following method to create web pages programmatically */
  /*
  public function onGeneralControllerAfterRouting() {
    switch ($this->pageUrl) {
      case "hello-world":
        $this->contentStructure = [
          "section_1" => [
            "plugin" => "WAI/SimpleContent/OneColumn",
            "settings" => [
              "heading" => "Welcome",
              "headingLevel" => 1,
              "content" => "Welcome, developer. This is a programmaticaly created Hello-World web page.",
            ],
          ],
        ];
      break;
    }
  }
  */
}

class AdminPanel extends \Surikata\Core\AdminPanel\Loader {
}
