<?php

namespace MyEcommerceProject;

class Web extends \Surikata\Core\Web\Loader {
  public function __construct($config, $adminPanel = NULL) {
    $this->registerPluginFolder(__DIR__."/Plugins");
    $this->registerThemeFolder(__DIR__."/Themes");
    parent::__construct($config, $adminPanel);
  }

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
  public function onBeforePluginsLoaded() {
    parent::onBeforePluginsLoaded();
    $this->registerPluginFolder(__DIR__."/Plugins");
  }
}
