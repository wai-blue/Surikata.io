<?php

namespace Surikata\Installer;

use \Symfony\Component\Yaml\Yaml;
class WebsiteContentGenerator {
  public $adminPanel;

  public $themeName = "";
  public $domainsToInstall = [];
  public $domainIdOffset = 0;
  public $domainName = "";
  public $domainSlug = "";
  public $themeObject = NULL;
  public $websiteCommonPanels = [];
  public $installationConfig = "";

  public $adminPanelDictionary = NULL;

  public function __construct($adminPanel, $domainsToInstall, $installationConfig) {
    $this->adminPanel = $adminPanel;
    $this->domainsToInstall = $domainsToInstall;
    $this->installationConfig = $installationConfig;
  }

  public function translate(string $original, string $context = "Installer") {
    // // A domain is linked to the "language index".
    // // A "language index" can represent any language.
    // // Default installation uses following languages:
    // //   LanguageIndex = 1 => English
    // //   LanguageIndex = 2 => Slovensky
    // //   LanguageIndex = 3 => Cesky

    // $languageIndex = $this->domainCurrentlyGenerated["languageIndex"];

    // // languageIndex == 1 is not translated
    // if ($languageIndex == 1) {
    //   return $original;
    // }

    // if (empty($original)) {
    //   return "";
    // }

    // if (empty($languageIndex)) {
    //   $this->adminPanel->console->warning("Translate: Destination language not set for `{$original}`.");
    //   return $original;
    // }

    // if (empty($this->adminPanelDictionary[$languageIndex])) {
    //   require(__DIR__."/../content/dictionary/adminpanel-{$languageIndex}.php");
    //   $this->adminPanelDictionary[$languageIndex] = $dictionary;
    // }

    // if (empty($this->adminPanelDictionary[$languageIndex])) {
    //   $this->adminPanel->console->warning("Translate: Dictionary for `{$languageIndex}` is empty.");
    //   return $original;
    // }

    // if (empty($this->adminPanelDictionary[$languageIndex][$original])) {
    //   $this->adminPanel->console->warning("Translate: `{$original}` is not translated to `{$languageIndex}`.");
    //   return $original;
    // }

    // return $this->adminPanelDictionary[$languageIndex][$original];

    if (empty($original)) {
      return "";
    }

    if ($this->domainCurrentlyGenerated["languageIndex"] == 1) {
      return $original;
    }

    $translated = "";

    foreach ($this->dictionary as $item) {
      if ($item["context"] == $context && $item["original"] == $original) {
        $translated = $item["translated"];
      }
    }

    if (empty($translated)) {
      $this->adminPanel->console->warning("Translate: `{$original}` (context: {$context}) is not translated to `{$this->domainName}`.");
      return $original;
    }

    return $translated;

  }

  public function copyAssets() {
    mkdir("{$this->adminPanel->config['files_dir']}/products/");

    copy(
      __DIR__."/../content/images/favicon.png",
      "{$this->adminPanel->config['files_dir']}/favicon.png"
    );

    for ($i = 1; $i <= 10; $i++) {
      copy(
        __DIR__."/../content/images/product_{$i}.jpg",
        "{$this->adminPanel->config['files_dir']}/products/{$i}.jpg",
      );
    }

    copy(
      __DIR__."/../content/images/your-logo.png",
      "{$this->adminPanel->config['files_dir']}/your-logo.png",
    );

    $imagesToCopy = [
      "cardpay.jpg",
      "tatrabanka.jpg",
      "posta.svg",
      "ups.svg",
    ];
    foreach ($imagesToCopy as $item) {
      copy(
        __DIR__."/../content/images/".$item,
        "{$this->adminPanel->config['files_dir']}/".$item,
      );
    }

  }

  public function generateMenuItems($idMenu, $items, $idParent = 0) {
    $websiteMenuItemModel = new \ADIOS\Widgets\Website\Models\WebMenuItem($this->adminPanel);
    foreach ($items as $item) {
      $idItem = $websiteMenuItemModel->insertRow([
        "id_menu" => $idMenu,
        "id_parent" => $idParent,
        "title" => $this->translate($item["title"]),
        "url" => $this->translate($item["url"]),
        "expand_product_categories" => $item["expand_product_categories"] ?? FALSE,
        "order_index" => (int) ($item["order_index"] ?? 0),
      ]);

      if (is_array($item["sub"]) && count($item["sub"]) > 0) {
        $this->generateMenuItems($idMenu, $item["sub"], $idItem);
      }
    }
  }

  public function generateWebsiteContent($domainIndex, $themeName) {
    $this->themeName = $themeName;
    $this->domainCurrentlyGenerated = $this->domainsToInstall[$domainIndex];
    $this->domainName = $this->domainCurrentlyGenerated['name'];
    $this->domainSlug = $this->domainCurrentlyGenerated['slug'];
    $this->domainIdOffset = $domainIndex * 100;

    $this->themeObject = $this->adminPanel->widgets['Website']->themes[$themeName];
    $this->dictionary = $this->loadDictionary($domainIndex);

    $this->installDictionary();

    $languageIndex = $this->domainCurrentlyGenerated["languageIndex"];

    $websiteMenuModel = new \ADIOS\Widgets\Website\Models\WebMenu($this->adminPanel);
    $websiteWebPageModel = new \ADIOS\Widgets\Website\Models\WebPage($this->adminPanel);
    $websiteWebRedirectModel = new \ADIOS\Widgets\Website\Models\WebRedirect($this->adminPanel);

    // web - menu

    $yamlMenuFolders = [
      __DIR__."/../content/menus",
      "{$this->themeObject->myRootFolder}/Install/menus",
    ];

    foreach ($this->adminPanel->pluginObjects as $pluginObject) {
      $yamlMenuFolders[] = "{$pluginObject->myRootFolder}/Install/menus";
    }

    $menus = [];

    foreach ($yamlMenuFolders as $folder) {
      if (!is_dir($folder)) continue;

      $files = scandir($folder);
      foreach ($files as $file) {
        if (in_array($file, [".", ".."])) continue;

        $yaml = file_get_contents("{$folder}/{$file}");

        $tmp = Yaml::parse($yaml);

        if (!is_array($menus[$file])) {
          $menus[$file] = $tmp;
        } else {
          $menus[$file]["items"] = array_merge($menus[$file]["items"], $tmp["items"]);
        }

      }
    }

    $i = 1;
    foreach ($menus as $menuName => $menu) {
      $idMenu = $websiteMenuModel->insertRow([
        "id" => $this->domainIdOffset + $i,
        "domain" => $this->domainName,
        "name" => $this->translate($menu["title"]),
      ]);

      $this->generateMenuItems($idMenu, $menu["items"]);

      $menus[$menuName]["id"] = $idMenu;

      $i++;
    }

    // web - stranky

    $yamlPageFolders = [
      ["Installer", __DIR__."/../content/pages"],
      [$this->themeName, "{$this->themeObject->myRootFolder}/Install/pages"],
    ];

    foreach ($this->adminPanel->pluginObjects as $pluginObject) {
      $yamlPageFolders[] = [$pluginObject->name, "{$pluginObject->myRootFolder}/Install/pages"];
    }

    $pages = [];

    foreach ($yamlPageFolders as $folderData) {
      list($folderContext, $folder) = $folderData;

      if (!is_dir($folder)) continue;

      $files = scandir($folder);
      foreach ($files as $file) {
        if (in_array($file, [".", ".."])) continue;

        $yaml = file_get_contents("{$folder}/{$file}");

        preg_match_all('/menu\("(.*?)"\)/', $yaml, $m);
        foreach ($m[0] as $key => $value) {
          $yaml = str_replace($m[0][$key], $menus[$m[1][$key]]["id"], $yaml);
        }

        preg_match_all('/translate\(("[^"]+")[, ]*?("[^"]+")?\)/', $yaml, $m);
        foreach ($m[0] as $key => $value) {
          $tmpOriginal = trim($m[1][$key], '"');
          $tmpContext = trim($m[2][$key], '"');

          if (empty($tmpContext)) {
            $tmpContext = $folderContext;
          }

          $yaml = str_replace(
            $m[0][$key],
            $this->translate($tmpOriginal, $tmpContext),
            $yaml
          );
        }

        $page = Yaml::parse($yaml);
        $page["title"] = $this->translate($page["title"] ?? "", $folderContext);
        $page["url"] = $this->translate($page["url"] ?? "", $folderContext);

        $pages[$file] = $page;
      }
    }

    foreach ($pages as $page) {
      $websiteWebPageModel->insertRow([
        "domain" => $this->domainName,
        "name" => $page["title"],
        "seo_title" => $page["title"],
        "seo_description" => $page["title"],
        "url" => $page["url"],
        "publish_always" => 1,
        "content_structure" => json_encode([
          "layout" => $page["layout"],
          "panels" => $page["panels"],
        ]),
      ]);
    }

    // web - presmerovania

    $websiteWebRedirectModel->insertRow([
      "domain" => $this->domainName,
      "from_url" => "",
      "to_url" => "//{% ROOT_URL %}/".$this->translate("home"),
      "type" => 302,
    ]);

    // web - nastavenia

    $webSettings = Yaml::parse(
      file_get_contents(__DIR__."/../content/settings/{$languageIndex}.yml")
    );

    $webSettings["companyInfo"] = [
      "slogan" => $this->translate("Custom e-Commerce solutions"),
      "contactPhoneNumber" => "+421 111 222 333",
      "contactEmail" => "info@{$this->installationConfig['http_host']}",
      "logo" => "your-logo.png",
      "urlFacebook" => "https://surikata.io",
      "urlTwitter" => "https://surikata.io",
      "urlYouTube" => "https://surikata.io",
      "urlInstagram" => "https://surikata.io"
    ];

    $webSettings["design"] = array_merge(
      $this->themeObject->getDefaultColorsAndStyles($this),
      [
        "theme" => $themeName,
      ]
    );

    $this->adminPanel->saveConfig([
      "settings" => [
        "web" => [
          $this->domainName => $webSettings
        ],
      ]
    ]);

    // theme onAfterInstall
    $this->themeObject->onAfterInstall($this);

  }

  public function installPlugins() {
    foreach ($this->adminPanel->pluginObjects as $pluginObject) {
      $pluginObject->install($this);
    }
  }

  public function installPluginsOnce() {
    foreach ($this->adminPanel->pluginObjects as $pluginObject) {
      $pluginObject->installOnce($this);
    }
  }

  public function loadDictionary($domainIndex) {
    $languageIndex = (int) $this->domainsToInstall[$domainIndex]["languageIndex"];

    if ($languageIndex == 1) return;

    include(__DIR__."/../content/dictionary/{$languageIndex}.php");

    $yamlDictionaryFolders = [
      ["Installer", __DIR__."/../content/dictionary"],
      [$this->themeName, "{$this->themeObject->myRootFolder}/Install/dictionary"],
    ];

    foreach ($this->adminPanel->pluginObjects as $pluginObject) {
      $yamlDictionaryFolders[] = [$pluginObject->name, "{$pluginObject->myRootFolder}/Install/dictionary"];
    }

    foreach ($yamlDictionaryFolders as $folderData) {
      list($folderContext, $folder) = $folderData;

      if (!is_file("{$folder}/{$languageIndex}.yml")) continue;

      $yaml = file_get_contents("{$folder}/{$languageIndex}.yml");

      $tmp = Yaml::parse($yaml);

      foreach ($tmp as $original => $translated) {
        $dictionary[] = [
          "context" => $folderContext,
          "original" => $original,
          "translated" => $translated,
        ];
      }
    }

    return $dictionary;
  }

  public function installDictionary() {
    $translationModel = new \ADIOS\Widgets\Website\Models\WebTranslation($this->adminPanel);

    foreach ($this->dictionary as $item) {
      $translationModel->insertRow([
        "domain" => $this->domainName,
        "context" => $item["context"],
        "original" => $item["original"],
        "translated" => $item["translated"],
      ]);
    }
  }
}