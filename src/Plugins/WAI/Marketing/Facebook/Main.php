<?php

namespace Surikata\Plugins\WAI\Marketing {
  class Facebook extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\Marketing {

  class Facebook extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "Facebook Marketing Tools",
        "logo" => "facebook-logo.png",
        "description" => "Standard marketing tools like sitemap.xml or DataLayer.",
      ];
    }

  }
}