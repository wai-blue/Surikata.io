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

  public function expandPanelsDefinition($panels) {
    $panelsExpanded = [];

    foreach ($panels as $tmpPanelName => $value) {
      $panelsExpanded[$tmpPanelName] = [];

      if (is_string($value)) {
        $panelsExpanded[$tmpPanelName]["plugin"] = $value;
      } else {
        $panelsExpanded[$tmpPanelName]["plugin"] = $value[0];
        if (isset($value[1])) {
          $panelsExpanded[$tmpPanelName]["settings"] = $value[1];
        }
      }
    }

    return $panelsExpanded;
  }

  public function webPageSimpleText($url, $title) {
    return [
      "section_1" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => $title,
          "content" => file_get_contents(__DIR__."/../content/PageTexts/{$url}.html"),
        ]
      ],
    ];
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

      if (is_array($item["sub"])) {
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
    $menus = [
      "header" => [
        "title" => "Header Menu",
        "items" => [
          [
            "title" => "Home",
            "url" => "home",
            "sub" => [
              [
                "title" => "About us",
                "url" => "about-us",
                "sub" => [],
              ],
            ],
          ],
          [
            "title" => "Products",
            "url" => "products",
            "expand_product_categories" => TRUE,
            // "sub" => [
            //   [
            //     "title" => "We recommend",
            //     "url" => "we-recommend",
            //     "sub" => [],
            //   ],
            // ],
          ],
          [
            "title" => "Blog",
            "url" => "blog",
            "sub" => [],
          ],
          [
            "title" => "Contact",
            "url" => "contact",
            "sub" => [],
          ],
        ],
      ],
      "footer" => [
        "title" => "Footer Menu",
        "items" => [
          [
            "title" => "About us",
            "url" => "about-us",
            "sub" => [],
          ],
          [
            "title" => "Contact",
            "url" => "contact",
            "sub" => [],
          ],
        ],
      ],
    ];

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
    $this->websiteCommonPanels[$this->domainName] = [
      "header" => ["WAI/Common/Header"],
      "navigation" => [
        "WAI/Common/Navigation",
        [
          "menuId" => $menus["header"]["id"],
          "homepageUrl" => $this->translate("home"),
          "showCategories" => TRUE,
          "showBreadcrumbs" => TRUE,
        ],
      ],
      "footer" => [ 
        "WAI/Common/Footer",
        [
          "mainMenuId" => $menus["header"]["id"],
          "secondaryMenuId" => $menus["footer"]["id"],
          "mainMenuTitle" => $this->translate("Pages"),
          "secondaryMenuTitle" => $this->translate("Our Company"),
          "showContactAddress" => 0,
          "showContactEmail" => 1,
          "showContactPhoneNumber" => 1,
          "contactTitle" => $this->translate("Contact us"),
          "showPayments" => 1,
          "showSocialMedia" => 1,
          "showSecondaryMenu" => 1,
          "showMainMenu" => 1,
          "showBlogs" => 1,
          "Newsletter" => 1,
          "blogsTitle" => $this->translate("Recent blogs"),
        ] 
      ],
    ];

    if ($this->domainSlug == "hello-world") {
      $webPages = [
        "home|WithoutSidebar|Home" => array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => "Hello World!",
              "content" => "
                <p>Welcome to Surikata.io.</p>
                <p>
                  Hello developer!<br/>
                  <br/>
                  Welcome to your first project built on Surikata.io. You are now reading the home page of a very simple
                  HelloWorld theme. Read carefully these tutorials if you want to become a real Surikata.io master.<br/>
                  <br/>
                  We wish you good luck and happy programming.<br/>
                  <br/>
                  Surikata.io team.
                </p>
                <p>
                  <a href='one-column'>Click here</a> to open a sample page using the WAI/SimpleContent/OneColumn plugin.
                </p>
              ",
            ]
          ],
        ]),
        "one-column|WithoutSidebar|One Column" => array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => "Hello World!",
              "content" => file_get_contents(__DIR__."/../content/PageTexts/o-nas.html"),
            ]
          ],
        ]),
      ];
    } else {

      $webPages = [

        // home
        "home|WithoutSidebar|Home" => $this->themeObject->getDefaultWebPageContent("home", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => ["WAI/Misc/Slideshow", ["speed" => 1000]],
          "section_2" => [
            "WAI/SimpleContent/Snippet",
            [
              "snippetName" => "homepage-after-slideshow",
            ],
          ],
          "section_3" => [
            "WAI/SimpleContent/H2",
            [
              "heading" => $this->translate("We recommend"),
            ],
          ],
          "section_4" => [
            "WAI/Product/FilteredList",
            [
              "filterType" => "recommended",
              "layout" => "tiles",
              "product_count" => 6,
            ],
          ],
          "section_5" => [
            "WAI/SimpleContent/TwoColumns",
            [
              "column1Content" => file_get_contents(__DIR__."/../content/PageTexts/lorem-ipsum-1.html"),
              "column1Width" => 4,
              "column2Content" => file_get_contents(__DIR__."/../content/PageTexts/lorem-ipsum-2.html"),
              "column2Width" => 8,
              "column2CSSClasses" => "text-right",
            ],
          ],
          "section_6" => [
            "WAI/SimpleContent/H2",
            [
              "heading" => $this->translate("Discount"),
            ],
          ],
          "section_7" => [
            "WAI/Product/FilteredList",
            [
              "filterType" => "on_sale",
              "layout" => "tiles",
              "product_count" => 6,
            ],
          ],
          "section_8" => [
            "WAI/SimpleContent/TwoColumns",
            [
              "column1Content" => file_get_contents(__DIR__."/../content/PageTexts/lorem-ipsum-2.html"),
              "column1Width" => 8,
              "column2Content" => file_get_contents(__DIR__."/../content/PageTexts/lorem-ipsum-1.html"),
              "column2Width" => 4,
              "column2CSSClasses" => "text-right",
            ],
          ],
          "section_9" => ["WAI/SimpleContent/Snippet", ["snippetName" => "homepage-modal"]],
        ]),

        // about-us
        "about-us|WithoutSidebar|About us" => $this->themeObject->getDefaultWebPageContent("about-us", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => $this->translate("About us"),
              "content" => file_get_contents(__DIR__."/../content/PageTexts/o-nas.html"),
            ]
          ],
          "section_2" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => $this->translate("Welcome"),
              "content" => file_get_contents(__DIR__."/../content/PageTexts/o-nas.html"),
            ]
          ],
        ]),

        // contact
        "contact|WithoutSidebar|Contact" => $this->themeObject->getDefaultWebPageContent("contact", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => ["WAI/Misc/ContactPage"],
        ]),

        // search
        "search|WithoutSidebar|Search" => $this->themeObject->getDefaultWebPageContent("search", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/Misc/WebsiteSearch",
            [
              "heading" => $this->translate("Search"),
              "numberOfResults" => 10,
              "searchInProducts" => "name_lang,brief_lang,description_lang",
              "searchInProductCategories" => "name_lang",
              "searchInBlogs" => "name,content",
            ]
          ],
        ]),

        // products
        "products|WithLeftSidebar|Products" => $this->themeObject->getDefaultWebPageContent("products", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "sidebar" => [
            "WAI/Product/Filter",
            [
              "showProductCategories" => 1,
              "layout" => "sidebar",
              "showProductCategories" => 1,
              "showBrands" => 1,
              "showFeaturesFilter" => 1,
            ]
          ],
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => ["WAI/Product/Catalog", ["defaultItemsPerPage" => 6]],
        ]),

        "we-recommend|WithoutSidebar|We recommend" => $this->themeObject->getDefaultWebPageContent("we-recommend", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => [
            "WAI/SimpleContent/H2",
            [
              "heading" => $this->translate("Discounts")
            ]
          ],
          "section_3" => [
            "WAI/Product/FilteredList",
            [
              "filterType" => "on_sale",
              "layout" => "tiles",
              "product_count" => 99,
            ],
          ],
          "section_4" => [
            "WAI/SimpleContent/H2",
            [
              "heading" => $this->translate("Sale out"),
            ]
          ],
          "section_5" => [
            "WAI/Product/FilteredList",
            [
              "filterType" => "sale_out",
              "layout" => "tiles",
              "product_count" => 99,
            ],
          ],
        ]),

        // product detail
        "|WithoutSidebar|Product detail" => $this->themeObject->getDefaultWebPageContent("product", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/Common/Breadcrumb",
            [
              "showHomePage" => 1,
            ],
          ],
          "section_2" => [
            "WAI/Product/Detail",
            [
              "show_similar_products" => 1,
              "show_accessories" => 1,
              "showAuthor" => 1,
            ],
          ],
        ]),

        // shopping cart
        "cart|WithoutSidebar|Cart" => $this->themeObject->getDefaultWebPageContent("cart", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Order/CartOverview",
        ]),

        // checkout
        "checkout|WithoutSidebar|Checkout" => $this->themeObject->getDefaultWebPageContent("checkout", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/Order/Checkout", [
              "enableVouchers" => 1
            ]
          ],
        ]),

        // order-confirmed
        "|WithoutSidebar|Order confirmed" => $this->themeObject->getDefaultWebPageContent("order-confirmed", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Order/Confirmation"
        ]),

        // order-payment-received
        "|WithoutSidebar|Order payment received" => $this->themeObject->getDefaultWebPageContent("order-payment-received", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Order/PaymentConfirmation"
        ]),

        // create-account
        "create-account|WithoutSidebar|Create Account" => $this->themeObject->getDefaultWebPageContent("create-account", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/Customer/Registration", [
              "showPrivacyTerms" => 1,
              "privacyTermsUrl" => "privacy-terms",
            ],
          ],
        ]),

        // create-account/confirmation
        "create-account/confirmation|WithoutSidebar|Create Account - Confirmation" => $this->themeObject->getDefaultWebPageContent("create-account-confirmation", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Customer/RegistrationConfirmation"
        ]),

        // my-account/validation
        "|WithoutSidebar|My account - Validation" => $this->themeObject->getDefaultWebPageContent("validate-account", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Customer/ValidationConfirmation"
        ]),

        // reset-password
        "reset-password|WithoutSidebar|Reset Password" => $this->themeObject->getDefaultWebPageContent("reset-password", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Customer/ForgotPassword"
        ]),

        // my-account
        "my-account|WithoutSidebar|My Account" => $this->themeObject->getDefaultWebPageContent("my-account", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Customer/Home",
        ]),

        // my-account/orders
        "my-account/orders|WithoutSidebar|My Account - Orders" => $this->themeObject->getDefaultWebPageContent("my-account-orders", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => "WAI/Customer/OrderList",
        ]),

        // login
        "sign-in|WithoutSidebar|My Account - Sign in" => $this->themeObject->getDefaultWebPageContent("sign-in", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/Customer/Login",
            [
              "showPrivacyTerms" => 1,
              "privacyTermsUrl" => "privacy-terms",
            ],
          ],
        ]),

        // privacy-terms
        "privacy-terms|WithoutSidebar|Privacy Terms" => $this->themeObject->getDefaultWebPageContent("privacy-terms", "WithoutSidebar", $menus) ?? array_merge(
          $this->websiteCommonPanels[$this->domainName], [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => $this->translate("We value your privacy"),
              "content" => file_get_contents(__DIR__."/../content/PageTexts/o-nas.html"),
            ]
          ]
        ]),

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
    }

    foreach ($webPages as $webPageData => $webPagePanels) {
      list($tmpUrl, $tmpLayout, $tmpTitle) = explode("|", $webPageData);

      $websiteWebPageModel->insertRow([
        "domain" => $this->domainName,
        "name" => $this->translate($tmpTitle),
        "seo_title" => $this->translate($tmpTitle),
        "seo_description" => $this->translate($tmpTitle),
        "url" => $this->translate($tmpUrl),
        "publish_always" => 1,
        "content_structure" => json_encode([
          "layout" => $tmpLayout,
          "panels" => $this->expandPanelsDefinition($webPagePanels),
        ]),
      ]);
    }

    $websiteWebRedirectModel->insertRow([
      "domain" => $this->domainName,
      "from_url" => "",
      // "to_url" => "//".$this->installationConfig['http_host'].$this->installationConfig['rewrite_base'].$this->domainSlug."/".$this->translate("home"),
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