<?php

namespace Surikata\Plugins\WAI\Common {
  class Header extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;
      $this->websiteRenderer->logTimestamp("Header getTWigParams #1");
      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Common {
  class Header extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("HTML Header"),
      ];
    }
  }
}