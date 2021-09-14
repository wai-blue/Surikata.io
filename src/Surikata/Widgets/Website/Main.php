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
      $sub = [];
      foreach ($this->adios->config['widgets']['Website']['domains'] as $domain => $domainInfo) {
        $sub[] = [
          // "fa_icon" => "fas fa-flag",
          "title" => $domainInfo["name"],
          "onclick" => "desktop_update('Website/{$domain}/Pages');",
          "sub" => [
            [
              "title" => $this->translate("Pages"),
              "onclick" => "desktop_update('Website/{$domain}/Pages');",
            ],
            [
              "title" => $this->translate("Redirects"),
              "onclick" => "desktop_update('Website/{$domain}/Redirects');",
            ],
            [
              "title" => $this->translate("Profile"),
              "onclick" => "window_render('Website/{$domain}/Profile');",
            ],
            [
              "title" => $this->translate("Design"),
              "onclick" => "window_render('Website/{$domain}/Design');",
            ],
            [
              "title" => $this->translate("Menu"),
              "onclick" => "desktop_update('Website/{$domain}/Menu');",
            ],
            [
              "title" => $this->translate("Online marketing"),
              "onclick" => "window_render('Website/{$domain}/SEO');",
            ],
            [
              "title" => $this->translate("Legal disclaimers"),
              "onclick" => "window_render('Website/{$domain}/LegalDisclaimers');",
            ],
            [
              "title" => "Emails",
              "onclick" => "window_render('Website/{$domain}/Emails');",
            ],
            [
              "title" => "Translations",
              "onclick" => "desktop_update('Website/{$domain}/Translations');",
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
          "params" => [ "domain" => '$1' ],
        ],
        '/^Website\/(.+)\/Profile$/' => [
          "action" => 'Website/Profile',
          "params" => [ "domain" => '$1' ],
        ],
        '/^Website\/(.+)\/SEO$/' => [
          "action" => 'Website/SEO',
          "params" => [ "domain" => '$1' ],
        ],
        '/^Website\/(.+)\/LegalDisclaimers$/' => [
          "action" => 'Website/LegalDisclaimers',
          "params" => [ "domain" => '$1' ],
        ],
        '/^Website\/(.+)\/Emails$/' => [
          "action" => 'Website/Emails',
          "params" => [ "domain" => '$1' ],
        ],
        '/^Website\/Media$/' => [
          "action" => 'UI/FileBrowser',
          "params" => [ "mode" => "select" ]
        ],
      ]);
    }
  }

  public function rebuildSitemap($domain) {
    // 1. vsetky zozbierane URL prejdem cez pluginy na webstrankach
    $urlsToSitemap = [];

    $webPages = $this->adios
      ->getModel("Widgets/Website/Models/WebPage")
      ->where("domain", "=", $domain)
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
      ->where("domain", "=", $domain)
      ->get()
      ->toArray()
    ;

    foreach ($redirects as $redirect) {
      $urlsToSitemap[$redirect["from_url"]]["redirect"] = [
        $redirect["to_url"],
        $redirect["type"]
      ];
    }

    // 4. zapisem do cache
    if (!is_dir("{$this->adios->config['tmp_dir']}/sitemap_cache")) {
      mkdir("{$this->adios->config['tmp_dir']}/sitemap_cache", 0775);
    }

    $h = fopen("{$this->adios->config['tmp_dir']}/sitemap_cache/{$domain}.json", "w");
    fwrite($h, json_encode($urlsToSitemap));
    fclose($h);
  }

  public function rebuildSitemapForAllDomains() {
    foreach (array_keys($this->adios->config['widgets']['Website']['domains']) as $domain) {
      $this->rebuildSitemap($domain);
    }
  }

  public function loadSitemapForDomain($domain) {
    $cacheFile = "{$this->adios->config['tmp_dir']}/sitemap_cache/{$domain}.json";
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