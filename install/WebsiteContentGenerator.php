<?php

class WebsiteContentGenerator {
  public $adminPanel;

  public function __construct($adminPanel, $slideshowImageSet, $domainsToInstall) {
    $this->adminPanel = $adminPanel;
    $this->slideshowImageSet = $slideshowImageSet;
    $this->domainsToInstall = $domainsToInstall;
  }

  public function translate($string) {
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
      $this->adminPanel->console->warning("Translate: Destination language not set.");
      return $string;
    }

    if (empty($this->dictionary[$languageIndex])) {
      require(__DIR__."/content/lang/{$languageIndex}.php");
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
    mkdir(__DIR__."/../upload/blogs/");
    mkdir(__DIR__."/../upload/products/");
    mkdir(__DIR__."/../upload/slideshow/");

    copy(
      __DIR__."/content/images/favicon.png",
      "{$this->adminPanel->config['files_dir']}/favicon.png"
    );

    for ($i = 1; $i <= 7; $i++) {
      copy(
        __DIR__."/content/images/category_{$i}.png",
        "{$this->adminPanel->config['files_dir']}/blogs/{$i}.png",
      );
    }
    for ($i = 1; $i <= 10; $i++) {
      copy(
        __DIR__."/content/images/product_{$i}.jpg",
        "{$this->adminPanel->config['files_dir']}/products/{$i}.jpg",
      );
    }
    for ($i = 1; $i <= 3; $i++) {
      copy(
        __DIR__."/content/images/slideshow/{$this->slideshowImageSet}/{$i}.jpg",
        "{$this->adminPanel->config['files_dir']}/slideshow/{$i}.jpg",
      );
    }

    copy(
      __DIR__."/content/images/your-logo.png",
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
        __DIR__."/content/images/".$item,
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
    $domainName = $this->domainCurrentlyGenerated['name'];
    $domainSlug = $this->domainCurrentlyGenerated['slug'];
    $themeObject = $this->adminPanel->widgets['Website']->themes[$themeName];
    $sampleContentDir = __DIR__."/content";
    $idOffset = $domainIndex * 100;

    $blogCatalogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel);
    $blogTagModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag($this->adminPanel);
    $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($this->adminPanel);
    $slideshowModel = new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\HomepageSlideshow($this->adminPanel);
    $newsModel = new \ADIOS\Plugins\WAI\News\Models\News($this->adminPanel);
    $websiteMenuModel = new \ADIOS\Widgets\Website\Models\WebMenu($this->adminPanel);
    $websiteMenuItemModel = new \ADIOS\Widgets\Website\Models\WebMenuItem($this->adminPanel);
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
        "id" => $idOffset + $i,
        "domain" => $domainName,
        "name" => $this->translate($menu["title"]),
      ]);

      $this->generateMenuItems($idMenu, $menu["items"]);

      $menus[$menuName]["id"] = $idMenu;

      $i++;
    }

    // web - stranky

    $websiteCommonPanels[$domainName] = [
      "header" => [
        "plugin" => "WAI/Common/Header"
      ],
      "navigation" => [
        "plugin" => "WAI/Common/Navigation",
        "settings" => [
          "menuId" => $menus["header"]["id"],
          "homepageUrl" => $this->translate("home"),
          "showCategories" => TRUE,
        ],
      ],
      "footer" => [ 
        "plugin" => "WAI/Common/Footer", 
        "settings" => [ 
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

    if ($domainSlug == "hello-world") {
      $webPages = [
        "home|WithoutSidebar|Home" => [
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
        ],
        "one-column|WithoutSidebar|One Column" => [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => "Hello World!",
              "content" => file_get_contents(__DIR__."/content/PageTexts/o-nas.html"),
            ]
          ],
        ],
      ];
    } else {
      $webPages = [

        // home
        "home|WithoutSidebar|Home" => [
          "section_1" => ["WAI/Misc/Slideshow", ["speed" => 1000]],
          "section_2" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => $this->translate("Welcome"),
              "headingLevel" => 1,
              "content" => file_get_contents(__DIR__."/../content/PageTexts/lorem-ipsum-1.html"),
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
          ]
        ],

        // about-us
        "about-us|WithoutSidebar|About us" => [
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
        ],
        "contact|WithoutSidebar|Contact" => [
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => "",
              "content" => file_get_contents(__DIR__."/../content/PageTexts/kontakt_sk.html"),
            ]
          ],
        ],

        // search
        "search|WithoutSidebar|Search" => [
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
        ],

        // products
        "products|WithLeftSidebar|Products" => [
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
        ],

        "we-recommend|WithoutSidebar|We recommend" => [
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
        ],

        // product detail
        "|WithoutSidebar|Product detail" => [
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
        ],

        // shopping cart
        "cart|WithoutSidebar|Cart" => [
          "section_1" => "WAI/Order/CartOverview",
        ],

        // checkout
        "checkout|WithoutSidebar|Checkout" => [
          "section_1" => [
            "WAI/Order/Checkout", [
              "enableVouchers" => 1
            ]
          ],
        ],

        // order-confirmed
        "|WithoutSidebar|Order confirmed" => [
          "section_1" => "WAI/Order/Confirmation"
        ],

        // create-account
        "create-account|WithoutSidebar|Create Account" => [
          "section_1" => [
            "WAI/Customer/Registration", [
              "showPrivacyTerms" => 1,
              "privacyTermsUrl" => "privacy-terms",
            ],
          ],
        ],

        // create-account/confirmation
        "create-account/confirmation|WithoutSidebar|Create Account - Confirmation" => [
          "section_1" => "WAI/Customer/RegistrationConfirmation"
        ],

        // my-account/validation
        "|WithoutSidebar|My account - Validation" => [
          "section_1" => "WAI/Customer/ValidationConfirmation"
        ],

        // reset-password
        "reset-password|WithoutSidebar|Reset Password" => [
          "section_1" => "WAI/Customer/ForgotPassword"
        ],

        // my-account
        "my-account|WithoutSidebar|My Account" => [
          "section_1" => "WAI/Customer/Home",
        ],

        // my-account/orders
        "my-account/orders|WithoutSidebar|My Account - Orders" => [
          "section_1" => "WAI/Customer/OrderList",
        ],

        // login
        "sign-in|WithoutSidebar|My Account - Sign in" => [
          "section_1" => [
            "WAI/Customer/Login",
            [
              "showPrivacyTerms" => 1,
              "privacyTermsUrl" => "privacy-terms",
            ],
          ],
        ],

        // privacy-terms
        "privacy-terms|WithoutSidebar|Privacy Terms" => [
          "section_1" => [
            "WAI/SimpleContent/OneColumn",
            [
              "heading" => $this->translate("We value your privacy"),
              "content" => file_get_contents(__DIR__."/content/PageTexts/o-nas.html"),
            ]
          ]
        ],

        // news
        "news|WithLeftSidebar|News" => [
          "sidebar" => ["WAI/News", ["contentType" => "sidebar"]],
          "section_1" => ["WAI/News", ["contentType" => "listOrDetail"]],
        ],

        // blogs - list
        "blog|WithLeftSidebar|Blog" => [
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
        ],

        // blog - detail
        "|WithLeftSidebar|Blog" => [
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
        ],

      ];
    }

    foreach ($webPages as $webPageData => $webPagePanels) {
      list($tmpUrl, $tmpLayout, $tmpTitle) = explode("|", $webPageData);
      $tmpPanels = [];
      foreach ($webPagePanels as $tmpPanelName => $value) {
        $tmpPanels[$tmpPanelName] = [];

        if (is_string($value)) {
          $tmpPanels[$tmpPanelName]["plugin"] = $value;
        } else {
          $tmpPanels[$tmpPanelName]["plugin"] = $value[0];
          if (isset($value[1])) {
            $tmpPanels[$tmpPanelName]["settings"] = $value[1];
          }
        }
      }

      $websiteWebPageModel->insertRow([
        "domain" => $domainName,
        "name" => $this->translate($tmpTitle),
        "seo_title" => $this->translate($tmpTitle),
        "seo_description" => $this->translate($tmpTitle),
        "url" => $this->translate($tmpUrl),
        "publish_always" => 1,
        "content_structure" => json_encode([
          "layout" => $tmpLayout,
          "panels" => array_merge($websiteCommonPanels[$domainName], $tmpPanels),
        ]),
      ]);
    }

    $websiteWebRedirectModel->insertRow([
      "domain" => $domainName,
      "from_url" => "",
      "to_url" => "//".$_SERVER['HTTP_HOST'].REWRITE_BASE.$domainSlug."/".$this->translate("home"),
      "type" => 302,
    ]);

    $this->adminPanel->widgets["Website"]->rebuildSitemap($domainName);

    // nastavenia webu

    $this->adminPanel->saveConfig([
      "settings" => [
        "web" => [
          $domainName => [
            "companyInfo" => [
              "slogan" => "Môj nový eshop: {$domainName}",
              "contactPhoneNumber" => "+421 111 222 333",
              "contactEmail" => "info@{$_SERVER['HTTP_HOST']}",
              "logo" => "your-logo.png",
              "urlFacebook" => "https://surikata.io",
              "urlTwitter" => "https://surikata.io",
              "urlYouTube" => "https://surikata.io",
              "urlInstagram" => "https://surikata.io"
            ],
            "design" => array_merge(
              $themeObject->getDefaultColorsAndStyles(),
              [
                "theme" => $themeName,
                "headerMenuID" => $idOffset + 1,
                "footerMenuID" => $idOffset + 2,
              ]
            ),
            "legalDisclaimers" => [
              "generalTerms" => "Bienvenue. VOP!",
              "privacyPolicy" => "Bienvenue. OOU!",
              "returnPolicy" => "Bienvenue. RP!",
            ],
            "emails" => [
              "signature" => "<p>{$domainName} - <a href='http://{$domainName}' target='_blank'>{$domainName}</a></p>",
              "after_order_confirmation_SUBJECT" => "{$domainName} - objednávka č. {% number %}",
              "after_order_confirmation_BODY" => file_get_contents(__DIR__."/../content/PageTexts/emails/orderBody_sk.html"),
              "after_registration_SUBJECT" => "{$domainName} - Overte Vašu emailovú adresu",
              "after_registration_BODY" => file_get_contents(__DIR__."/../content/PageTexts/emails/registrationBody_sk.html"),
              "forgot_password_SUBJECT" => "{$domainName} - Obnovenie hesla",
              "forgot_password_BODY" => file_get_contents(__DIR__."/../content/PageTexts/emails/forgotPasswordBody_sk.html")
            ],
          ],
        ],
      ]
    ]);

    $themeObject->onAfterInstall();

    /////////////////////////////////////////////////////////////////

    // Blogs
    $i = 1;
    foreach (scandir($sampleContentDir) as $file) {
      if (in_array($file, [".", ".."])) continue;

      $tmpContent = file_get_contents("{$sampleContentDir}/{$file}");

      $blogCatalogModel->insertRow([
        "id" => $idOffset + $i,
        "name" => pathinfo($file, PATHINFO_FILENAME),
        "content" => $tmpContent,
        "perex" => mb_substr($tmpContent, 0, 50),
        "image" => "blogs/{$i}.png",
        "created_at" => date("Y-m-d"),
        "id_user" => 1,
      ]);

      $i++;
    }

    // Slideshow

    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => $this->translate("Welcome"),
      "description" => $this->translate("Your best online store"),
      "image" => "slideshow/1.jpg",
      "button_url" => "produkty",
      "button_text" => $this->translate("Start shopping"),
    ]);
    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => $this->translate("Discounts"),
      "description" => $this->translate("We have something special for your"),
      "image" => "slideshow/2.jpg",
      "button_url" => $this->translate("discounts"),
      "button_text" => $this->translate("Show discounts"),
    ]);
    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => $this->translate("Check our luxury products"),
      "description" => $this->translate("We sell only most-rated and reliable products"),
      "image" => "slideshow/3.jpg",
    ]);

    // News

    $newsModel->insertRow([
      "title" => $this->translate("Welcome to our online store"),
      "perex" => $this->translate("We built our online store using Surikata.io."),
      "content" => $this->translate("We built our online store using Surikata.io."),
      "domain" => $domainName,
    ]);
 }
}