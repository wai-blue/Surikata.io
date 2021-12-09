<?php

namespace Surikata\Installer;

class WebsiteContentGenerator {
  public $adminPanel;
  public $domainsToInstall = [];
  public $domainIdOffset = 0;
  public $domainName = "";
  public $domainSlug = "";
  public $themeObject = [];
  public $websiteCommonPanels = [];
  public $installationConfig = "";

  public function __construct($adminPanel, $domainsToInstall, $installationConfig) {
    $this->adminPanel = $adminPanel;
    $this->domainsToInstall = $domainsToInstall;
    $this->installationConfig = $installationConfig;
  }

  public function translate(string $string) {
    // A domain is linked to the "language index".
    // A "language index" can represent any language.
    // Default installation uses following languages:
    //   LanguageIndex = 1 => English
    //   LanguageIndex = 2 => Slovensky
    //   LanguageIndex = 3 => Cesky

    $languageIndex = $this->domainCurrentlyGenerated["languageIndex"];

    // languageIndex == 1 is not translated
    if ($languageIndex == 1) {
      return $string;
    }

    if (empty($string)) {
      return "";
    }

    if (empty($languageIndex)) {
      $this->adminPanel->console->warning("Translate: Destination language not set for `{$string}`.");
      return $string;
    }

    if (empty($this->dictionary[$languageIndex])) {
      require(__DIR__."/../content/lang/{$languageIndex}.php");
      $this->dictionary[$languageIndex] = $dictionary;
    }

    if (empty($this->dictionary[$languageIndex])) {
      $this->adminPanel->console->warning("Translate: Dictionary for `{$languageIndex}` is empty.");
      return $string;
    }

    if (empty($this->dictionary[$languageIndex][$string])) {
      $this->adminPanel->console->warning("Translate: `{$string}` is not translated to `{$languageIndex}`.");
      return $string;
    }

    return $this->dictionary[$languageIndex][$string];

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
      ]);

      if (is_array($item["sub"]) && count($item["sub"]) > 0) {
        $this->generateMenuItems($idMenu, $item["sub"], $idItem);
      }
    }
  }

  public function generateWebsiteContent($domainIndex, $themeName) {
    $this->domainCurrentlyGenerated = $this->domainsToInstall[$domainIndex];
    $this->domainName = $this->domainCurrentlyGenerated['name'];
    $this->domainSlug = $this->domainCurrentlyGenerated['slug'];
    $this->domainIdOffset = $domainIndex * 100;

    $this->themeObject = $this->adminPanel->widgets['Website']->themes[$themeName];

    $websiteMenuModel = new \ADIOS\Widgets\Website\Models\WebMenu($this->adminPanel);
    $websiteWebPageModel = new \ADIOS\Widgets\Website\Models\WebPage($this->adminPanel);
    $websiteWebRedirectModel = new \ADIOS\Widgets\Website\Models\WebRedirect($this->adminPanel);

    // web - menu

    $yamlMenuFolders = [
      __DIR__."/../content/menus",
      "{$this->themeObject->myRootFolder}/Install/menus",
    ];

    $menus = [];

    foreach ($yamlMenuFolders as $folder) {
      if (!is_dir($folder)) continue;

      $files = scandir($folder);
      foreach ($files as $file) {
        if (in_array($file, [".", ".."])) continue;
        $yaml = file_get_contents("{$folder}/{$file}");
        $menus[$file] = \Symfony\Component\Yaml\Yaml::parse($yaml);
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

    $webPages = [

      // news
      "news|WithLeftSidebar|News" => $this->themeObject->getDefaultWebPageContent("news", "WithoutSidebar", $menus) ?? array_merge(
        $this->websiteCommonPanels[$this->domainName], [
        "sidebar" => ["WAI/News", ["contentType" => "sidebar"]],
        "section_1" => ["WAI/News", ["contentType" => "listOrDetail"]],
      ]),

      // blogs - list
      "blog|WithLeftSidebar|Blog" => $this->themeObject->getDefaultWebPageContent("blog", "WithoutSidebar", $menus) ?? array_merge(
        $this->websiteCommonPanels[$this->domainName], [
        "sidebar" => [
          "WAI/Blog/Sidebar", [
            "showRecent" => 1,
            "showArchive" => 1,
            "showAdvertising" => 1,
          ],
        ],
        "section_1" => [
          "WAI/Common/Breadcrumb",
          [
            "showHomePage" => 1,
          ],
        ],
        "section_2" => [
          "WAI/Blog/Catalog",
          [
            "itemsPerPage" => 3,
            "showAuthor" => 1,
          ],
        ],
      ]),

      // blog - detail
      "|WithLeftSidebar|Blog" => $this->themeObject->getDefaultWebPageContent("blog-detail", "WithoutSidebar", $menus) ?? array_merge(
        $this->websiteCommonPanels[$this->domainName], [
        "sidebar" => [
          "WAI/Blog/Sidebar",
          [
            "showRecent" => 1,
            "showArchive" => 1,
            "showAdvertising" => 1,
          ],
        ],
        "section_1" => [
          "WAI/Common/Breadcrumb",
          [
            "showHomePage" => 1,
          ],
        ],
        "section_2" => "WAI/Blog/Detail",
      ]),

    ];

    //

    $yamlPageFolders = [
      __DIR__."/../content/pages",
      "{$this->themeObject->myRootFolder}/Install/pages",
    ];

    $pages = [];

    foreach ($yamlPageFolders as $folder) {
      if (!is_dir($folder)) continue;

      $files = scandir($folder);
      foreach ($files as $file) {
        if (in_array($file, [".", ".."])) continue;

        $yaml = file_get_contents("{$folder}/{$file}");
        $yaml = str_replace("{{ menuHeaderId }}", $menus["header.yml"]["id"], $yaml);
        $yaml = str_replace("{{ menuFooterId }}", $menus["footer.yml"]["id"], $yaml);

        preg_match_all('/menu\("(.*?)"\)/', $yaml, $m);
        foreach ($m[0] as $key => $value) {
          $yaml = str_replace($m[0][$key], $menus[$m[1][$key]]["id"], $yaml);
        }

        preg_match_all('/translate\("(.*?)"\)/', $yaml, $m);

        foreach ($m[0] as $key => $value) {
          $yaml = str_replace($m[0][$key], $this->translate($m[1][$key]), $yaml);
        }

        $pages[$file] = \Symfony\Component\Yaml\Yaml::parse($yaml);
      }
    }

    foreach ($pages as $page) {

      $tmpPanels = [];
      foreach ($page["panels"] as $tmpPanel) {
        $tmpPanels[$tmpPanel["name"]] = [
          "plugin" => $tmpPanel["plugin"],
          "settings" => $tmpPanel["settings"],
        ];
      }

      $websiteWebPageModel->insertRow([
        "domain" => $this->domainName,
        "name" => $this->translate($page["title"] ?? ""),
        "seo_title" => $this->translate($page["title"] ?? ""),
        "seo_description" => $this->translate($page["title"] ?? ""),
        "url" => $this->translate($page["url"] ?? ""),
        "publish_always" => 1,
        "content_structure" => json_encode([
          "layout" => $page["layout"],
          "panels" => $tmpPanels,
        ]),
      ]);
    }

    //

    $websiteWebRedirectModel->insertRow([
      "domain" => $this->domainName,
      "from_url" => "",
      "to_url" => "//{% ROOT_URL %}/".$this->translate("home"),
      "type" => 302,
    ]);

    $emailsContentFolder = __DIR__."/../content/emails/language-index-{$this->domainCurrentlyGenerated["languageIndex"]}";
    $emails = [
      "signature" => "<p>{$this->domainName} - <a href='http://{$this->domainName}' target='_blank'>{$this->domainName}</a></p>",
      "after_order_confirmation_SUBJECT" => file_get_contents("{$emailsContentFolder}/after_order_confirmation_SUBJECT.txt"),
      "after_order_confirmation_BODY" => file_get_contents("{$emailsContentFolder}/after_order_confirmation_BODY.html"),
      "after_registration_SUBJECT" => file_get_contents("{$emailsContentFolder}/after_registration_SUBJECT.txt"),
      "after_registration_BODY" => file_get_contents("{$emailsContentFolder}/after_registration_BODY.html"),
      "forgotten_password_SUBJECT" => file_get_contents("{$emailsContentFolder}/forgot_password_SUBJECT.txt"),
      "forgotten_password_BODY" => file_get_contents("{$emailsContentFolder}/forgot_password_BODY.html"),
    ];

    // nastavenia webu
    $this->adminPanel->saveConfig([
      "settings" => [
        "web" => [
          $this->domainName => [
            "companyInfo" => [
              "slogan" => $this->translate("slogan"),
              "contactPhoneNumber" => "+421 111 222 333",
              "contactEmail" => "info@{$this->installationConfig['http_host']}",
              "logo" => "your-logo.png",
              "urlFacebook" => "https://surikata.io",
              "urlTwitter" => "https://surikata.io",
              "urlYouTube" => "https://surikata.io",
              "urlInstagram" => "https://surikata.io"
            ],
            "design" => array_merge(
              $this->themeObject->getDefaultColorsAndStyles($this),
              [
                "theme" => $themeName,
                "headerMenuID" => $this->domainIdOffset + 1,
                "footerMenuID" => $this->domainIdOffset + 2,
              ]
            ),
            "legalDisclaimers" => [
              "generalTerms" => "Bienvenue. VOP!",
              "privacyPolicy" => "Bienvenue. OOU!",
              "returnPolicy" => "Bienvenue. RP!",
            ],
            "emails" => $emails,
          ],
        ],
      ]
    ]);

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

  public function installDictionary($domainIndex) {
    $domainName = $this->domainsToInstall[$domainIndex]["name"];
    $languageIndex = (int) $this->domainsToInstall[$domainIndex]["languageIndex"];

    if ($languageIndex == 1) return;

    require(__DIR__."/../content/lang-themes/{$languageIndex}.php");

    $translationModel = new \ADIOS\Widgets\Website\Models\WebTranslation($this->adminPanel);

    foreach ($dictionary as $item) {
      $translationModel->insertRow([
        "domain" => $domainName,
        "context" => $item[0],
        "original" => $item[1],
        "translated" => $item[2],
      ]);
    }
  }
}