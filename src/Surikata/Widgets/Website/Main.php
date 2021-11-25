<?php

namespace ADIOS\Widgets;

class Website extends \ADIOS\Core\Widget {
  var $themes = [];

  public function init() {

    foreach ($this->adios->websiteRenderer->themeFolders as $themeFolder) {
      foreach (scandir($themeFolder) as $file) {
        if (strpos($file, ".") !== FALSE) continue;
        $this->getTheme($file);
      }
    }

    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ONLINE_MARKETING)) {
      $domains = $this->adios->getAvailableDomains();

      $sub = [];
      foreach ($domains as $domain => $domainInfo) {
        $sub[] = [
          "title" => $domainInfo["name"],
          "onclick" => "desktop_update('Website/{$domainInfo['name']}/Pages');",
          "sub" => [
            [
              "title" => $this->translate("Pages"),
              "onclick" => "desktop_update('Website/{$domainInfo['name']}/Pages');",
            ],
            [
              "title" => $this->translate("Redirects"),
              "onclick" => "desktop_update('Website/{$domainInfo['name']}/Redirects');",
            ],
            [
              "title" => $this->translate("Menu"),
              "onclick" => "desktop_update('Website/{$domainInfo['name']}/Menu');",
            ],
            [
              "title" => $this->translate("Company Info"),
              "onclick" => "window_render('Website/{$domainInfo['name']}/CompanyInfo');",
            ],
            [
              "title" => $this->translate("Design"),
              "onclick" => "window_render('Website/{$domainInfo['name']}/Design');",
            ],
            [
              "title" => $this->translate("Online marketing"),
              "onclick" => "window_render('Website/{$domainInfo['name']}/OnlineMarketingAndSEO');",
            ],
            [
              "title" => $this->translate("Legal disclaimers"),
              "onclick" => "window_render('Website/{$domainInfo['name']}/LegalDisclaimers');",
            ],
            [
              "title" => $this->translate("Emails"),
              "onclick" => "window_render('Website/{$domainInfo['name']}/Emails');",
            ],
            [
              "title" => $this->translate("Translations"),
              "onclick" => "desktop_update('Website/{$domainInfo['name']}/Translations');",
            ],
          ],
        ];
      }

      // $sub[] = [
      //   "fa_icon" => "fas fa-folder-open",
      //   "title" => $this->translate("Files and media"),
      //   "onclick" => "window_render('Website/Media');",
      // ];
      // $sub['WebsitePluginsMenu'] = [
      //   "fa_icon" => "fas fa-puzzle-piece",
      //   "title" => "Plugins",
      //   "sub" => []
      // ];
      // $sub[] = [
      //   "title" => "Modern templates",
      //   "onclick" => "window.open('https://uicookies.com/shop-website-template');",
      // ];

      $this->adios->config['desktop']['sidebarItems']['Website'] = [
        "fa_icon" => "fas fa-globe",
        "title" => $this->translate("Websites"),
        "onclick" => "desktop_update('Website/".reset($domains)["name"]."/Pages');",
        "sub" => $sub,
      ];

      $this->adios->config['desktop']['sidebarItems']['FilesAndMedia'] = [
        "fa_icon" => "fas fa-folder-open",
        "title" => $this->translate("Files and media"),
        "onclick" => "desktop_update('Website/Media');",
      ];

      $this->adios->addRouting([
        '/^Website\/(.+)\/Design$/' => [
          "action" => 'Website/Design',
          "params" => [ "domainName" => '$1' ],
        ],
        '/^Website\/(.+)\/CompanyInfo$/' => [
          "action" => 'Website/CompanyInfo',
          "params" => [ "domainName" => '$1' ],
        ],
        '/^Website\/(.+)\/OnlineMarketingAndSEO$/' => [
          "action" => 'Website/OnlineMarketingAndSEO',
          "params" => [ "domainName" => '$1' ],
        ],
        '/^Website\/(.+)\/LegalDisclaimers$/' => [
          "action" => 'Website/LegalDisclaimers',
          "params" => [ "domainName" => '$1' ],
        ],
        '/^Website\/(.+)\/Emails$/' => [
          "action" => 'Website/Emails',
          "params" => [ "domainName" => '$1' ],
        ],
        '/^Website\/Media$/' => [
          "action" => 'UI/FileBrowser',
          "params" => [ "mode" => "select" ]
        ],
      ]);
    }
  }

  public function rebuildSitemap($domainName) {
    $availableDomains = $this->adios->getAvailableDomains();

    $domain = [];

    foreach ($availableDomains as $tmpDomain) {
      if ($tmpDomain['name'] == $domainName) {
        $domain = $tmpDomain;
      }
    }

    if (empty($domain['name'])) return;

    // 1. vsetky zozbierane URL prejdem cez pluginy na webstrankach
    $urlsToSitemap = [];

    $webPages = $this->adios
      ->getModel("Widgets/Website/Models/WebPage")
      ->where("domain", "=", $domain['name'])
      ->get()
      ->toArray()
    ;

    foreach ($webPages as $webPage) {
      $contentStructure = @json_decode($webPage['content_structure'], TRUE);

      if (!empty($webPage['url'])) {
        $urlsToSitemap[$webPage['url']] = [
          "template" => "Layouts/{$contentStructure['layout']}",
          "idWebPage" => $webPage['id'],
          "urlVariables" => [
            "idWebPage" => $webPage['id'],
          ],
        ];
      }

      foreach ($contentStructure['panels'] as $panelName => $panelSettings) {
        if (!empty($panelSettings["plugin"])) {
          $tmpPlugin = $this->adios->getPlugin($panelSettings["plugin"]);

          if (is_object($tmpPlugin) && method_exists($tmpPlugin, "getSiteMap")) {

            $tmpUrls = $tmpPlugin->getSiteMap(
              $panelSettings["settings"],
              $webPage['url']
            );

            foreach ($tmpUrls as $tmpUrl => $tmpUrlVariables) {
              $tmpUrlVariables["idWebPage"] = $webPage['id'];
              $urlsToSitemap[$tmpUrl] = [
                "template" => "Layouts/{$contentStructure['layout']}",
                "idWebPage" => $webPage['id'],
                "urlVariables" => $tmpUrlVariables,
              ];
            }
          }
        }
      }
    }

    // 3. prejdem redirects
    $redirects = $this->adios
      ->getModel("Widgets/Website/Models/WebRedirect")
      ->where("domain", "=", $domain['name'])
      ->get()
      ->toArray()
    ;

    foreach ($redirects as $redirect) {
      $redirect["to_url"] = str_replace("{% ROOT_URL %}", $domain['rootUrl'], $redirect["to_url"]);
      $urlsToSitemap[$redirect["from_url"]]["redirect"] = [
        $redirect["to_url"],
        $redirect["type"]
      ];
    }

    // 4. zapisem do cache
    if (!is_dir(CACHE_DIR."/sitemap")) {
      mkdir(CACHE_DIR."/sitemap", 0775);
    }

    $h = fopen(CACHE_DIR."/sitemap/{$domain['name']}.json", "w");
    fwrite($h, json_encode($urlsToSitemap));
    fclose($h);
  }

  public function rebuildSitemapForAllDomains() {
    if (is_dir(CACHE_DIR."/sitemap")) {
      @unlink(CACHE_DIR."/sitemap");
    }

    foreach ($this->adios->getAvailableDomains() as $domain) {
      $this->rebuildSitemap($domain['name']);
    }
  }

  public function loadSitemapForDomain($domain) {
    $cacheFile = CACHE_DIR."/sitemap/{$domain}.json";
    if (!is_file($cacheFile)) {
      $this->rebuildSitemap($domain);
    }

    $sitemapCached = @json_decode(@file_get_contents($cacheFile), TRUE);

    if (!is_array($sitemapCached)) {
      $sitemapCached = [];
    }

    return $sitemapCached;
  }

  public function getThemeClassName($theme) {
    return "Surikata\\Themes\\".str_replace("/", "\\", $theme);
  }

  public function getTheme($theme) {
    if (!isset($this->themes[$theme])) {
      $themeClassName = $this->getThemeClassName($theme);
      if (class_exists($themeClassName)) {
        $this->themes[$theme] = new $themeClassName($this);
      } else {
        throw new \ADIOS\Core\Exceptions\GeneralException("Can't find theme '{$theme}'.");
      }
    }

    return $this->themes[$theme];
  }

}