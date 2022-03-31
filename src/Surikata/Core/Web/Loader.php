<?php

namespace Surikata\Core\Web;

use \voku\helper\HtmlMin;

/**
 * Loader class for the Surikata engine. Encapsulates CASCADA for website presentation and ADIOS for administration
 */
class Loader extends \Cascada\Loader {

  /** Reference to ADIOS object. Enables API of the administration panel. */
  var $adminPanel;

  /** List of already loaded and created content plugins for the website presentation */
  var $pluginObjects = [];

  /** Stores the name of the theme chosen in the administration panel */
  var $themeName = "";

  /** Stores the path to the folder with Theme's files */
  var $themeDir = "";

  var $themeFolders = [];
  var $pluginFolders = [];

  var $paymentPlugins = [];
  var $deliveryPlugins = [];

  var $userLogged = NULL;

  var $pages;
  var $currentPage;

  var $domain;

  var $currentRenderedPlugin = NULL;

  var $translationCache = NULL;
  var $customerDataCache = [];
  var $publishedPagesCache = [];

  var $visualContentEditorEnabled = FALSE;
  var $visualContentEditorIframeToken = "";

  /** Stores objects of rendered plugins on current page */
  public array $currentPagePlugins = [];

  /**
   * Class constructor.
   * 
   * Does following:
   *   - create the ADIOS object into the $surikata property
   *   - load list of published sites (pages) from the database
   *     (uses Widgets/Website/Models/WebPage model)
   *   - loads the settings of the website from the database
   *     (uses Core/Models/Config model)
   *   - sets $themeName and $themeDir properties
   *   - initalizes CASCADA's Twig and introduces own Twig functions 'callSurikataMethod' and 'callPluginMethod'
   * 
   * @param array $config Configuration for the Surikata engine.
   * 
   */
  public function __construct($config, $adminPanel = NULL) {
    $this->currentRunHash = substr(md5(time().rand(1000, 9999)), 0, 5);

    $this->adminPanel = $adminPanel;

    if (is_object($this->adminPanel)) {
      $this->adminPanel->websiteRenderer = $this;
    }

    // parent::__construct
    parent::__construct($config);

    $this->registerPluginFolder(__DIR__."/../../../Plugins");
    $this->registerThemeFolder(__DIR__."/../../../Themes");

    if (is_object($this->adminPanel)) {

      // Ak nie je nastaveny adminPanel, tak websiteRenderer
      // nema odkial vediet, aka tema a aka stranka sa ma zobrazovat.
      // Neinicializuju sa teda properties potrebne pre renderovanie webu.

      $this->pages = $this->loadPublishedPages();
      $this->currentPage = NULL;

      $this->domain = $this->getDomainInfo($this->config["domainToRender"]);

      $this->adminPanel->webSettings = $this->adminPanel->config["settings"]["web"][$this->domain['name']];

      $this->themeName = $this->adminPanel->webSettings["design"]["theme"];

      $this->themeDir = "";
      foreach ($this->themeFolders as $themeFolder) {
        if (is_file("{$themeFolder}/{$this->themeName}/Main.php")) {
          $this->themeDir = "{$themeFolder}/{$this->themeName}";
        }
      }

      $this->assetsUrlMap["core/assets/js/variables.js"] = function($websiteRenderer, $url, $variables) {
        // dictionary
        $domainToRender = $this->config['domainToRender'];
        $translationModel = new \ADIOS\Widgets\Website\Models\WebTranslation($this->adminPanel);
        $dictionary = $translationModel->loadCache()[$domainToRender];
        
        echo "var __srkt_dict__ = JSON.parse('".ads(json_encode($dictionary))."');";

        // globalTwigParams
        echo "var globalTwigParams = JSON.parse('".ads(json_encode($this->getGlobalTwigParams()))."');";

        exit;
      };
      $this->assetsUrlMap["core/assets/js/plugins.js"] = function($websiteRenderer, $url, $variables) {
        $js =
          $this->renderPluginsAsset("js/api.js")
          .$this->renderPluginsAsset("js/dom.js")
        ;

        header("Content-type: text/js");
        header("ETag: ".md5($js));
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");
        header("Pragma: cache");
        header("Cache-Control: max-age={3600}");

        echo $js;
        exit();
      };
      $this->assetsUrlMap["core/assets/css/plugins.css"] = function($websiteRenderer, $url, $variables) {
        $css =
          $this->renderPluginsAsset("css/default.css")
        ;

        header("Content-type: text/css");
        header("ETag: ".md5($css));
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");
        header("Pragma: cache");
        header("Cache-Control: max-age={3600}");

        echo $css;
        exit();
      };
      $this->assetsUrlMap["core/assets/"] = SURIKATA_ROOT_DIR."/src/Surikata/Core/Assets/";
      $this->assetsUrlMap["theme/assets/"] = "{$this->themeDir}/Assets/";
      $this->assetsUrlMap["plugins/assets/"] = function($websiteRenderer, $url) { 
        $url = str_replace("plugins/assets/", "", $url);
        preg_match('/(.+?)\/~\/(.+)/', $url, $m);

        $plugin = $m[1];
        $asset = $m[2];
        foreach ($websiteRenderer->pluginFolders as $pluginFolder) {
          $file = "{$pluginFolder}/{$plugin}/Assets/{$asset}";
          if (is_file($file)) {
            return $file;
          }
        }
      };
      $this->assetsUrlMap["upload/image/resize/"] = function($websiteRenderer, $template) { 
        $template = str_replace("upload/image/resize/", "", $template);
        preg_match('/(\d+)\/(\d+)\/(.+)/', $template, $m);

        $requestedWidth = (int) $m[1];
        $requestedHeight = (int) $m[2];
        $fileName = urldecode($m[3]);

        $img = new \Surikata\Core\Web\ImageProcessor("{$websiteRenderer->adminPanel->config['files_dir']}/{$fileName}");

        if ($requestedWidth > 0 && $requestedHeight > 0) {
          $img->resize($requestedWidth, $requestedHeight);
        } elseif ($requestedWidth > 0) {
          $img->resizeToWidth($requestedWidth);
        } elseif ($requestedHeight > 0) {
          $img->resizeToWidth($requestedHeight);
        }

        $cachingTime = 3600;
        $headerExpires = "Expires: ".gmdate("D, d M Y H:i:s", time() + $cachingTime) . " GMT";
        $headerCacheControl = "Cache-Control: max-age={$cachingTime}";

        header($headerExpires);
        header("Pragma: cache");
        header($headerCacheControl);

        switch ($img->imageType) {
          case IMAGETYPE_JPEG: header('Content-Type: image/jpeg'); break;
          case IMAGETYPE_GIF: header('Content-Type: image/gif'); break;
          case IMAGETYPE_PNG: header('Content-Type: image/png'); break;
        }

        $img->output();

        exit();
      };

      $this->initTwig();

      // priklad volania v Twig sablone:
      //   {{ callSurikataMethod('methodName', [param1, param2]) }}
      // nasledne bude zavolana metoda: $___CASCADAObject->methodName($param1, $param2)
      $this->twig->addFunction(new \Twig\TwigFunction(
        'callSurikataMethod',
        function ($function, $params = []) {
          return call_user_func_array(
            [$this, $function],
            [$params]
          );
        }
      ));

      // podobny princip, ako callSurikataMethod, akurat sa vola metoda pluginu
      $this->twig->addFunction(new \Twig\TwigFunction(
        'callPluginMethod',
        function ($pluginName, $function, $params = []) {
          return call_user_func_array(
            [$this->getPlugin($pluginName), $function],
            [$params]
          );
        }
      ));

      // podobny princip, ako callSurikataMethod, akurat sa vola metoda pluginu
      $this->twig->addFunction(new \Twig\TwigFunction(
        'getUrlForPlugin',
        function ($pluginName, $urlParams = []) {
          $plugin = $this->getPlugin($pluginName);
          return $plugin->getWebPageUrl($urlParams);
        }
      ));

      $this->twig->addFunction(new \Twig\TwigFunction(
        'translate',
        function ($original, $context = NULL, $domain = NULL) {
          $this->translate($original, $context, $domain);
        }
      ));

      $this->twig->addFunction(new \Twig\TwigFunction(
        'insertSnippets',
        function ($snippetName, $renderParams = []) {
          return $this->renderSnippets($snippetName, $renderParams);
        }
      ));

      $this->twig->addFunction(new \Twig\TwigFunction(
        'insertSnippet',
        function ($pluginName, $snippetName, $renderParams = []) {
          return $this->renderSnippet($pluginName, $snippetName, $renderParams);
        }
      ));

      $this->twig->addFilter(new \Twig\TwigFilter(
        'formatPrice',
        function ($string) {
          return $this->adminPanel->locale->formatPrice($string);
        }
      ));

      $this->setRouter(new \Cascada\Router($this->getSiteMap()));
    }

  }
  

  public function validateOutputHtml() {
    // https://www.vzhurudolu.cz/prirucka/checklist
    // TODO: automaticka kontrola vystupneho HMTL na SEO parametre
    
    $regexpMustNotMatch = TRUE; // ak sa retazec v HTML nachaza, je problem
    $regexpMustMatch = FALSE; // ak sa retazec v HTML nenachaza, je problem

    $validationRegexps = [
      [
        "/<script>/i",
        "SCRIPT tag is found in HTML.",
        $regexpMustNotMatch
      ]
    ];

    foreach ($validationRegexps as $regexp) {
      $match = preg_match($regexp[0], $this->outputHtml);
      if (
        ($match && $regexp[2])
        || (!$match && !$regexp[2])
      ) {
        $this->adminPanel->console->warning($regexp[1], ["//{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}"]);
      }
    }
  }

  public function render() {
    try {
      parent::render();

      $outputFormat = ($_GET['__output'] ?? "");

      if ($outputFormat != "json" && $this->config['minifyOutputHtml'] ?? FALSE) {
        $htmlMinifier = new HtmlMin();
        $this->outputHtml = $htmlMinifier->minify($this->outputHtml);
      }
    } catch (
      \Illuminate\Database\QueryException
      | \ADIOS\Core\Exceptions\DBException
      $e
    ) {
      $errorHash = md5(date("YmdHis").$e->getMessage());
      $this->adminPanel->console->error("{$errorHash} ".$e->getMessage());
      return json_encode([
        "status" => "FAIL",
        "exception" => "SurikataCore",
        "error" => "Oops! Something went wrong with the database. See logs for more information. Error hash: {$errorHash}",
      ]);
    }

    if ($this->config['validateOutputHtml'] ?? FALSE) {
      $this->validateOutputHtml();
    }

    return $this->outputHtml;
  }

  /**
   * Loads the site map for CASCADA router.
   * 
   * @return array Site map definition for CASCADA router.
   * */
  public function getSiteMap() {
    $siteMap = [
      // controllers pouzite pri vsetkych URL
      "*" => [
        "controllers" => [
          new \Surikata\Core\Web\Controllers\UserProfile($this),
          new \Surikata\Core\Web\Controllers\General($this),
        ],
      ],

      // 404
      "NotFoundTemplate" => "404",

    ];

    $siteMapDomain = (new \ADIOS\Widgets\Website($this->adminPanel))->loadSitemapForDomain($this->config["domainToRender"]);

    $siteMap = array_merge($siteMap, $siteMapDomain);

    $siteMap = $this->adminPanel->dispatchEventToPlugins("onAfterSiteMap", [
      "siteMap" => $siteMap,
    ])["siteMap"];

    if (!is_array($siteMap)) $siteMap = [];

    return $siteMap;

  }

  public function renderSnippet($pluginName, $snippetName, $renderParams) {
    $html = "";

    $snippetTemplateFile1 = "Templates/Snippets/{$pluginName}.twig";
    $snippetTemplateFile2 = "Templates/Snippets/{$pluginName}/{$snippetName}.twig";

    $templateFile = "";
    if (is_file("{$this->themeDir}/{$snippetTemplateFile1}")) {
      $templateFile = $snippetTemplateFile1;
    } else if (is_file("{$this->themeDir}/{$snippetTemplateFile2}")) {
      $templateFile = $snippetTemplateFile2;
    }

    if (!empty($templateFile)) {
      $pluginTwigParams = [];
      $plugin = $this->getPlugin($pluginName);

      if (is_object($plugin)) {
        $pluginTwigParams = $plugin->getTwigParams($renderParams);
      }

      $snippetRenderParams = array_merge(
        (array)$this->currentRenderedPlugin->twigRenderParams,
        $renderParams,
        $pluginTwigParams
      );
      $snippetRenderParams["snippetName"] = $snippetName;
      $snippetRenderParams["system"]["availableVariables"] = array_keys($snippetRenderParams);

      $html = $this->twig
        ->render($templateFile, $snippetRenderParams)
      ;
    }

    return $html;
  }

  public function renderSnippets($snippetName, $renderParams) {
    $html = "";

    foreach ($this->adminPanel->plugins as $pluginName) {
      $html .= $this->renderSnippet($pluginName, $snippetName, $renderParams);
    }

    return $html;
  }

  public function onGeneralControllerAfterRouting() {
    // to be overriden
  }

  /**
   * Loads the list of published sites of the website managed by the user in the administration panel.
   *
   * @param string $domain Domainfor which the published pages should be loaded. Deafult: current domain to render.
   * 
   * @return array List of published sites.
   * */
  public function loadPublishedPages($domain = "") {
    if (empty($domain)) $domain = $this->config["domainToRender"];

    if (!isset($this->publishedPagesCache[$domain])) {
      $tmp = (new \ADIOS\Widgets\Website\Models\WebPage($this->adminPanel))
        ->where('domain', $domain)
        ->where('publish_always', '1')
        ->orWhere(function($q) {
          $q
            ->where('publish_from', '<=', date("Y-m-d"))
            ->where('publish_to', '>=', date("Y-m-d"))
          ;
        })
        ->get()
        ->toArray()
      ;

      $pages = [];
      foreach ($tmp as $value) {
        $pages[$value['id']] = $value;
      }

      $this->publishedPagesCache[$domain] = $pages;
    }

    $pages = $this->publishedPagesCache[$domain];

    return $pages;
  }

  public function registerThemeFolder($folder) {
    if (is_dir($folder) && !in_array($folder, $this->themeFolders)) {
      $this->themeFolders[] = realpath($folder);
    }
  }

  public function registerPluginFolder($folder) {
    if (is_dir($folder) && !in_array($folder, $this->pluginFolders)) {
      $this->pluginFolders[] = realpath($folder);;
    }
  }

  /**
   * Returns the object of the content plugin.
   * 
   * First checks if the object has already been created. If yes, simply returns it.
   * If not, creates it, stores it into the $plugins property and returns.
   * 
   * @param string pluginName Name of the plugin.
   * 
   * @return object Of class \Surikata\Plugin.
   */
  public function getPlugin($pluginName) {
    if (empty($pluginName)) return NULL;
    if (!$this->adminPanel->isPluginEnabled($pluginName)) return NULL;
    
    if (empty($this->pluginObjects[$pluginName])) {
      $pluginClassName = "\\Surikata\\Plugins\\".str_replace("/", "\\", $pluginName);
      if (class_exists($pluginClassName)) {
        $this->pluginObjects[$pluginName] = new $pluginClassName($this);
      }
    }

    return $this->pluginObjects[$pluginName];
  }

  public function getCurrentPagePluginSettings($pluginName, $panelName = "") {
    $pluginSettings = NULL;

    $contentStructure = @json_decode(($this->currentPage['content_structure'] ?? ""), TRUE);
    if (is_array($contentStructure)) {
      foreach ($contentStructure['panels'] as $tmpPanelName => $panelSettings) {
        if (!empty($panelName) && $tmpPanelName != $panelName) continue;
        if (!empty($panelSettings["plugin"]) && $panelSettings["plugin"] == $pluginName) {
          $pluginSettings = $panelSettings["settings"] ?? [];
          break;
        }
      }

    }

    return $pluginSettings;
  }

  public function getCurrentPagePlugins() {
    if (empty($this->currentPagePlugins)) {
      $contentStructure = @json_decode(($this->currentPage['content_structure'] ?? ""), TRUE);

      if (is_array($contentStructure)) {
        foreach ($contentStructure['panels'] as $panelName => $panelValues) {
          if ($panelValues['plugin'] != "") {
            $this->currentPagePlugins[$panelName] = $this->getPlugin($panelValues['plugin']);
          }
        }
      }
    }

    return $this->currentPagePlugins;
  }

  /**
   * Returns unique identifier of the customer / visitor of the website.
   * 
   * Not implemented yet. Returns 'CustUID'.
   * 
   * @return string Unique identifier of the customer
   * */
  public function getCustomerUID() {
    $cookieName = 'srkt-c-uid';

    if (empty($_COOKIE[$cookieName])) {
      $customerUID = uniqid(md5(time()).".", TRUE);
      setcookie($cookieName, $customerUID, time() + 3600 * 24 * 30);  /* expire in 1 month */
    } else {
      $customerUID = $_COOKIE[$cookieName];
    }
    
    return $customerUID;
  }

  public function getCurrentCustomerData() {
    $customerUID = $this->getCustomerUID();

    if (empty($this->customerDataCache[$customerUID])) {
      $customerModel = new \ADIOS\Widgets\Customers\Models\CustomerUID($this->adios);
      $this->customerDataCache[$customerUID] = $customerModel->getByCustomerUID($customerUID);
    }

    return $this->customerDataCache[$customerUID];
  }

  public function registerPaymentPlugin($pluginName) {
    $pluginClassName = "\\Surikata\\Plugins\\".str_replace("/", "\\", $pluginName);
    if (
      !in_array($pluginName, $this->paymentPlugins)
      && property_exists($pluginClassName, 'isPaymentPlugin')
      && $pluginClassName::$isPaymentPlugin ?? FALSE
    ) {
      $this->paymentPlugins[$pluginName] = $this->getPlugin($pluginName);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function getPaymentPlugins() {
    foreach ($this->adminPanel->plugins as $pluginName) {
      if (!in_array($pluginName, [".", ".."])) {
        $this->registerPaymentPlugin($pluginName);
      }
    }

    return $this->paymentPlugins;
  }

  public function registerDeliveryPlugin($pluginName) {
    $pluginClassName = "\\Surikata\\Plugins\\".str_replace("/", "\\", $pluginName);

    if (
      !in_array($pluginName, $this->deliveryPlugins)
      && property_exists($pluginClassName, 'isDeliveryPlugin')
      && $pluginClassName::$isDeliveryPlugin ?? FALSE
    ) {
      $this->deliveryPlugins[$pluginName] = $this->getPlugin($pluginName);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function getDeliveryPlugins() {
    foreach ($this->adminPanel->plugins as $pluginName) {
      if (!in_array($pluginName, [".", ".."])) {
        $this->registerDeliveryPlugin($pluginName);
      }
    }
    return $this->deliveryPlugins;
  }

  public function getAvailableDomains() {
    return $this->adminPanel->getAvailableDomains();
  }

  public function getDomainInfo($domainName) {
    return $this->adminPanel->getDomainInfo($domainName);
  }

  public function renderPluginsAsset($assetFilename) {
    $content = "";

    foreach ($this->adminPanel->plugins as $pluginName) {
      if (!in_array($pluginName, [".", ".."])) {
        foreach ($this->adminPanel->pluginFolders as $pluginFolder) {
          $file = "{$pluginFolder}/{$pluginName}/Assets/{$assetFilename}";
          if (is_file($file)) {
            $content .= file_get_contents($file) . "\n\n";
          }
        }
      }
    }

    return $content;
  }

  public function getGlobalTwigParams() {
    $globalTwigParams['filesUrl'] = $this->adminPanel->config['files_url'];
    $globalTwigParams['domain'] = $this->domain;
    $globalTwigParams['rootUrl'] = rtrim($this->rewriteBase, "/");

    $globalTwigParams["customerUID"] = $this->getCustomerUID();
    $globalTwigParams["currentYear"] = date("Y");
    $globalTwigParams["today"] = date("Y-m-d");
    $globalTwigParams["settings"] = [
      "web" => $this->adminPanel->webSettings,
      "plugins" => $this->adminPanel->config["settings"]["plugins"]
    ];
    $globalTwigParams["languageIndex"] = $this->domain['languageIndex'];
    $globalTwigParams["urlVariables"] = $this->urlVariables;
    $globalTwigParams["uploadedFileUrl"] = $this->adminPanel->config['files_url'];
    $globalTwigParams["header"] = [
      "metaKeywords" => $this->currentPage["seo_keywords"] ?? "",
      "metaDescription" => $this->currentPage["seo_description"] ?? "",
      "pageTitle" => $this->currentPage["seo_title"] ?? "",
    ];
    $globalTwigParams["locale"] = $this->adminPanel->locale->getAll();

    foreach ($this->adminPanel->plugins as $pluginName) {
      if (!in_array($pluginName, [".", ".."])) {
        $plugin = $this->getPlugin($pluginName);
        if (is_object($plugin)) {
          $pluginGlobalTwigParams = $plugin->getGlobalTwigParams();
          if (!empty($pluginGlobalTwigParams)) {
            $globalTwigParams[$pluginName] = $pluginGlobalTwigParams;
          }
        }
      }
    }

    return $globalTwigParams;
  }

  public function logTimestamp($message) {
    $this->adminPanel->console->logTimestamp("{$this->currentRunHash}, {$_SERVER['REQUEST_URI']}, {$message}", "web");
  }

  public function translate($original, $context = NULL, $domain = NULL) {
    if ($domain === NULL) {
      $domain = $this->config['domainToRender'];
    }

    $translationModel = new \ADIOS\Widgets\Website\Models\WebTranslation($this->adminPanel);

    if (
      $context === NULL
      && $this->currentRenderedPlugin !== NULL
    ) {
      $context = $this->currentRenderedPlugin->name;
    }

    $context = (string) $context;

    if ($this->translationCache === NULL) {
      $this->translationCache = $translationModel->loadCache();
    }

    if (!isset($this->translationCache[$domain][$context][$original])) {
      $translationModel->insertRow([
        "domain" => $domain,
        "context" => $context,
        "original" => $original,
        "translated" => "",
      ]);

        $this->translationCache[$domain][$context][$original] = $original;
    } else {
      $translatedText = $this->translationCache[$domain][$context][$original];
    }

    return empty($translatedText) ? $original : $translatedText;
  }

}
