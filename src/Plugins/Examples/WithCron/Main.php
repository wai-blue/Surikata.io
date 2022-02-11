<?php

// Popisat...

namespace Surikata\Plugins\Examples {
  class WithCron extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\Examples {
  class WithCron extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("WithCron example"),
      ];
    }
  }
}