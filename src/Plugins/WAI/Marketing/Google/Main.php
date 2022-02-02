<?php

namespace Surikata\Plugins\WAI\Marketing {
  class Google extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\Marketing {

  class Google extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "Google Marketing Tools",
        "logo" => "google-logo.png",
        "description" => "Standard marketing tools like sitemap.xml or DataLayer.",
      ];
    }

  }
}