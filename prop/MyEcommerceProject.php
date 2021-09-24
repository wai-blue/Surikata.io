<?php

namespace MyEcommerceProject;

/**
 * This class will be instantiated by the main ROOT/index.php file.
 * This is where your customizations of the frontend rendering engine
 * start.
 */
class Web extends \Surikata\Core\Web\Loader {
  public function __construct($config, $adminPanel = NULL) {
    $this->registerPluginFolder(__DIR__."/Plugins");
    $this->registerThemeFolder(__DIR__."/Themes");
    parent::__construct($config, $adminPanel);
  }

  /* Uncomment following method to create web pages programmatically */
  public function onGeneralControllerAfterRouting() {
    // switch ($this->pageUrl) {
    //   case "hello-world":
    //     $this->contentStructure = [
    //       "section_1" => [
    //         "plugin" => "WAI/SimpleContent/OneColumn",
    //         "settings" => [
    //           "heading" => "Welcome",
    //           "headingLevel" => 1,
    //           "content" => "Welcome, developer. This is a programmaticaly created Hello-World web page.",
    //         ],
    //       ],
    //     ];
    //   break;
    // }
  }
}

/**
 * This class will be instantiated by the main ROOT/admin/index.php file.
 * This is where your customizations of the backend rendering engine
 * start.
 */
class AdminPanel extends \Surikata\Core\AdminPanel\Loader {

  /* You can force desired user role here. */
  /* Only for testing & development purposes. */
  public function onUserAuthorised() {
    // $this->setUserRole(\MyEcommerceProject\AdminPanel::USER_ROLE_PRODUCT_MANAGER);
  }

  /* You can register your own plugins folder here. */
  public function onBeforePluginsLoaded() {
    parent::onBeforePluginsLoaded();
    $this->registerPluginFolder(__DIR__."/Plugins");
  }
}
