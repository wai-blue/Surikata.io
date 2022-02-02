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

    public function onGeneralControllerPreRender($event) {
      $settings = [];
      $domainName = $this->adios->websiteRenderer->domain['name'];
      foreach ($this->adios->config["settings"]["plugins"]["WAI"]["Marketing"]["Facebook"] as $key => $value) {
        if (strpos($key, "{$domainName}_") === 0) {
          $settings[str_replace("{$domainName}_", "", $key)] = $value;
        }
      }

      $this->adios->websiteRenderer->setTwigParams([
        "facebookMarketingTools" => $settings
      ]);

      return $event;
    }

    public function onAfterSiteMap($event) {
      $siteMap = $event['siteMap'];

      $siteMap["sitemap.xml"] = [
        "controllers" => [
          new \Surikata\Plugins\WAI\Marketing\Facebook\Controllers\SitemapXMLGenerator($this->adios->websiteRenderer),
        ],
        "template" => "Layouts/WithLeftSidebar",
      ];

      $event['siteMap'] = $siteMap;

      return $event; // forward event unchanged
    }
  }
}