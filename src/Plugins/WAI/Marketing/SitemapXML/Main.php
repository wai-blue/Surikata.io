<?php

namespace Surikata\Plugins\WAI\Marketing {
  class SitemapXML extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\WAI\Marketing {

  class SitemapXML extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "SitemapXML",
        "faIcon" => "fas fa-code",
        "description" => "Sitemap.xml or DataLayer.",
      ];
    }

    public function onGeneralControllerPreRender($event) {
      $settings = [];
      $domainName = $this->adios->websiteRenderer->domain['name'];
      foreach ($this->adios->config["settings"]["plugins"]["WAI"]["Marketing"]["SitemapXML"] as $key => $value) {
        if (strpos($key, "{$domainName}_") === 0) {
          $settings[str_replace("{$domainName}_", "", $key)] = $value;
        }
      }

      $this->adios->websiteRenderer->setTwigParams([
        "plugins" => [
          "WAI" => [
            "Marketing" => [
              "SitemapXML" => $settings
            ],
          ],
        ],
      ]);

      return $event;
    }

    public function onAfterSiteMap($event) {
      $siteMap = $event['siteMap'];

      $siteMap["sitemap.xml"] = [
        "controllers" => [
          new \Surikata\Plugins\WAI\Marketing\SitemapXML\Controllers\SitemapXMLGenerator($this->adios->websiteRenderer),
        ],
        "template" => "Layouts/WithLeftSidebar",
      ];

      $event['siteMap'] = $siteMap;

      return $event; // forward event unchanged
    }

  }
}