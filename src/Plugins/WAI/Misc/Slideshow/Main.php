<?php

namespace Surikata\Plugins\WAI\Misc {

  class Slideshow extends \Surikata\Core\Web\Plugin {

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
  class Slideshow extends \Surikata\Core\AdminPanel\Plugin {

    var $niceName = "Slideshow";

    public function getSettingsForWebsite() {
      return [
        "speed" => [
          "title" => "Speed",
          "type" => "varchar",
        ]
      ];
    }

    
  }
}