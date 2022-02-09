<?php

// This is a Hello World example plugin for Surikata.
// This plugin contains no settings, adds no variables to TWIG templates,
// has neither own models nor actions. This is the simplest Surikata plugin.
//
// You should notice following:
//
// 1. There are two namespaces:
//   - Surikata namespace for the frontend part of the plugin
//   - ADIOS namespace for the backend part of the plugin
//
// 2. Each namespace has one class:
//   - extend of \Surikata\Core\Web\Plugin - for the "Core/Web", or the frontend part
//   - extend of \Surikata\Core\AdminPanel\Plugin - for the "Core/AdminPanel", or the admin panel part
//
// 3. Mind also a "websiteRenderer" - an object of the CASCADA class that renders the
// website (the frontend).
//
// 4. Each "AdminPanel" part of the plugin must have its own manifest(). This
// method returns various information about the plugin that are used to identify
// plugin in the Admin Panel.
//
// 5. There is $this->translate() method used. However, this plugin does not have its dictionary.
// Therefore the translate() method only return the original string.

namespace Surikata\Plugins\Examples {
  class HelloWorld extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;
      $this->websiteRenderer->logTimestamp("Hello World getTwigParams");
      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\Examples {
  class HelloWorld extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Hello World example"),
      ];
    }
  }
}