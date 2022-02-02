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