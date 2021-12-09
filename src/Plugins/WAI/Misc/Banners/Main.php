<?php

namespace Surikata\Plugins\WAI\Misc {
  class Banners extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {
      return [
        "bannerGrid" => explode(",", str_replace(" ", "", $pluginSettings["bannerGrid"])),
      ];
    }
  }
}

namespace ADIOS\Plugins\WAI\Misc {
  class Banners extends \Surikata\Core\AdminPanel\Plugin {
    public function getSettingsForWebsite() {
      return [
        "bannerHeight" => [
          "title" => "Banner height",
          "unit" => "px",
          "type" => "int",
        ],
        "bannerGrid" => [
          "title" => "Banner grid",
          "description" => "
            Comma separated values.
            Each value represent width of a banner as a number of 1/12th of a full
            width. This is similar to Bootstrap.<br/>
            Examples:<br/>
            3,3,3,3 - 4 equaly wide banners<br/>
            6,3,3 - 1st banner is double-wide and then 2 equally wide banners follow<br/>
          ",
          "type" => "varchar",
        ]
      ];
    }
  }
}