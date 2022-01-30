<?php

namespace Surikata\Plugins\WAI\Common\MarketingTools\Controllers;

class SitemapXMLGenerator extends \Surikata\Core\Web\Controller {
  public function render() {
    
    $siteMap = $this->websiteRenderer->getSiteMap();
    $plugins = $this->adminPanel->getPlugins();

    $xml = "
      <?xml version='1.0' encoding='UTF-8'?>
      <urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>
    ";

    foreach ($siteMap as $url => $site) {
      if (isset($site['urlVariables']['idWebPage'])) {
        unset($site['urlVariables']['idWebPage']);
      }

      if (!empty($site['template']) && empty($site['urlVariables'])) {
        $xml .= "<url><loc>//{$this->websiteRenderer->domain['rootUrl']}/{$url}</loc></url>\n";
      }
    }

    foreach ($plugins as $pluginObject) {
      if (method_exists($pluginObject, "getSitemapXMLData")) {
        $sitemapXMLData = $pluginObject->getSitemapXMLData();
        foreach ($sitemapXMLData as $data) {
          $xml .= "<url><loc>//{$this->websiteRenderer->domain['rootUrl']}/{$data['url']}</loc></url>\n";
        }
      }
    }

    $xml .= "
      </urlset>
    ";

    return $xml;
  }
}