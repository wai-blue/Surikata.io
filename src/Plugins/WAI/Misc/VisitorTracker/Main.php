<?php

namespace Surikata\Plugins\WAI\Misc {

  class VisitorTracker extends \Surikata\Core\Web\Plugin {

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;
      
      $twigParams["slideShowSlides"] = (new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\UvodnaSlideshow($this->adminPanel))
        ->get()
        ->toArray()
      ;
      
      return $twigParams;
    }
  }

}

namespace ADIOS\Plugins\WAI\Misc {
  class VisitorTracker extends \Surikata\Core\AdminPanel\Plugin {

    var $niceName = "Visitor Tracker";

    public function getSettingsForWebsite() {
      return [
        "speed" => [
          "title" => "Speed",
          "type" => "varchar",
        ]
      ];
    }

    public function onGeneralControllerPreRender($event) {
      $controller = $event['controller'];
      $websiteRenderer = $controller->websiteRenderer;
      $customerUID = $websiteRenderer->getCustomerUID();

      if (!is_dir(PROJECT_ROOT_DIR."/data/visitor-tracker")) {
        mkdir(PROJECT_ROOT_DIR."/data/visitor-tracker", 0775);
      }

      if (!is_dir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y"))) {
        mkdir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y"), 0775);
      }

      if (!is_dir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y")."/".date("m"))) {
        mkdir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y")."/".date("m"), 0775);
      }

      if (!is_dir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y")."/".date("m")."/".date("d"))) {
        mkdir(PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y")."/".date("m")."/".date("d"), 0775);
      }

      file_put_contents(
        PROJECT_ROOT_DIR."/data/visitor-tracker/".date("Y")."/".date("m")."/".date("d")."/{$customerUID}.dat",
        pack("vV", $websiteRenderer->idWebPage, time()),
        FILE_APPEND
      );

      return $event;
    }

  }
}