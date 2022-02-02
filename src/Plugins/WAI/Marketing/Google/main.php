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

    public function onGeneralControllerPreRender($event) {
      $settings = [];
      $domainName = $this->adios->websiteRenderer->domain['name'];
      foreach ($this->adios->config["settings"]["plugins"]["WAI"]["Marketing"]["Google"] as $key => $value) {
        if (strpos($key, "{$domainName}_") === 0) {
          $settings[str_replace("{$domainName}_", "", $key)] = $value;
        }
      }
      
      $this->adios->websiteRenderer->setTwigParams([
        "plugins" => [
          "WAI" => [
            "Marketing" => [
              "Google" => $settings
            ],
          ],
        ],
      ]);

      return $event;
    }

  }
}