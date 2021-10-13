<?php

class WebsiteContentGenerator {
  public $adminPanel;

  public function __construct($adminPanel, $slideshowImageSet, $domainsToInstall) {
    $this->adminPanel = $adminPanel;
    $this->slideshowImageSet = $slideshowImageSet;
    $this->domainsToInstall = $domainsToInstall;
  }

  public function translate($str) {
    switch ($this->domainCurrentlyGenerated["languageIndex"]) {
      case 1:
        return "Home";
      break;
      case 2:
        return "Úvod";
      break;
      case 3:
        return "Wilkommen";
      break;
      default:
        return "???";
      break;
    }
  }

  public function webPageSimpleText($url, $title) {
    return [
      "section_1" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => $title,
          "content" => file_get_contents(__DIR__."/../SampleData/PageTexts/{$url}.html"),
        ]
      ],
    ];
  }

  public function copyAssets() {
    mkdir(__DIR__."/../upload/blogs/");
    mkdir(__DIR__."/../upload/products/");
    mkdir(__DIR__."/../upload/slideshow/");

    for ($i = 1; $i <= 7; $i++) {
      copy(
        __DIR__."/SampleData/images/category_{$i}.png",
        "{$this->adminPanel->config['files_dir']}/blogs/category_{$i}.png",
      );
    }
    for ($i = 1; $i <= 10; $i++) {
      copy(
        __DIR__."/SampleData/images/product_{$i}.jpg",
        "{$this->adminPanel->config['files_dir']}/products/product_{$i}.jpg",
      );
    }
    for ($i = 1; $i <= 3; $i++) {
      copy(
        __DIR__."/SampleData/images/slideshow/{$this->slideshowImageSet}/{$i}.jpg",
        "{$this->adminPanel->config['files_dir']}/slideshow/{$i}.jpg",
      );
    }

    copy(
      __DIR__."/SampleData/images/your-logo.png",
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
        __DIR__."/SampleData/images/".$item,
        "{$this->adminPanel->config['files_dir']}/".$item,
      );
    }

  }

  public function generateWebsiteContent($domainIndex, $themeName) {
    $this->domainCurrentlyGenerated = $this->domainsToInstall[$domainIndex];
    $domainName = $this->domainCurrentlyGenerated['name'];
    $domainSlug = $this->domainCurrentlyGenerated['slug'];
    $themeObject = $this->adminPanel->widgets['Website']->themes[$themeName];
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

    // Blogs
    $blogCatalogModel->insertRow(["id" => $idOffset + 1, "name" => "Ako vznikol vesmír?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_7.png", "created_at" => date("Y-m-d"), "id_user" => 1]);
    $blogCatalogModel->insertRow(["id" => $idOffset + 2, "name" => "Blog?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_3.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")),  "id_user" => 2]);
    $blogCatalogModel->insertRow(["id" => $idOffset + 3, "name" => "Lorem Ipsum", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_6.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")), "id_user" => 1]);
    $blogCatalogModel->insertRow(["id" => $idOffset + 4, "name" => "Ahoj Blog", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_1.png", "created_at" => date("Y-m-d", strtotime("8.8.2000")), "id_user" => 3]);

    // Blogs tags
    $blogTagModel->insertRow(["id" => $idOffset + 1, "name" => "Žltý ({$domainIndex})", "description" => "Žltá farba"]);
    $blogTagModel->insertRow(["id" => $idOffset + 2, "name" => "Modrý ({$domainIndex})", "description" => "Modrá farba"]);
    $blogTagModel->insertRow(["id" => $idOffset + 3, "name" => "Červený ({$domainIndex})", "description" => "Červená farba"]);

    // Blogs tags assignment
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 1, "id_blog" => $idOffset + 1]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 2, "id_blog" => $idOffset + 1]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 3, "id_blog" => $idOffset + 1]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 2, "id_blog" => $idOffset + 2]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 1, "id_blog" => $idOffset + 3]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 3, "id_blog" => $idOffset + 4]);
    $blogTagAssignmentModel->insertRow(["id_tag" => $idOffset + 2, "id_blog" => $idOffset + 4]);

    // Slideshow

    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => "Vitajte",
      "description" => "Všetko pre váš online nákup",
      "image" => "slideshow/1.jpg",
      "button_url" => "produkty",
      "button_text" => "Začať nakupovať",
    ]);
    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => "Aktuálne zľavy",
      "description" => "Využite naše aktuálne zľavy",
      "image" => "slideshow/2.jpg",
      "button_url" => "akcie-a-zlavy",
      "button_text" => "Zobraziť akcie a zľavy",
    ]);
    $slideshowModel->insertRow([
      "domain" => $domainName,
      "heading" => "Top sortiment",
      "description" => "Ponúkame najkvalitnejší sortiment",
      "image" => "slideshow/3.jpg",
    ]);

    // News

    $newsModel->insertRow([
      "title" => "Prvá novinka",
      "content" => "Skutočne prvá novinka na Surikate Online Store",
      "perex" => "Krátky popis stručnej novinky",
      "domain" => $domainName,
      "image" => "",
      "show_from" => "20.6.2021",
    ]);

    $newsModel->insertRow([
      "title" => "Druhá novinka",
      "content" => "Surikata rastie - druhá novinka",
      "perex" => "Popis druhej novinky pre rastúcu Surikatu",
      "domain" => $domainName,
      "image" => "",
      "show_from" => "22.6.2021",
    ]);

    // web - menu

    $websiteMenuModel->insertRow(["id" => $idOffset + 1, "domain" => $domainName, "name" => "Menu v hlavičke ({$domainName})"]);
    $websiteMenuModel->insertRow(["id" => $idOffset + 2, "domain" => $domainName, "name" => "Menu v päte stránky ({$domainName})"]);

    // web - menu items
    $tmpHomepageID = $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => 0, "title" => $this->translate("Úvod"), "url" => "uvod"]);
    $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => $tmpHomepageID, "title" => "O nás", "url" => "o-nas"]);
    $tmpProduktyID = $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => 0, "title" => "Produkty", "url" => "produkty"]);
    $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => $tmpProduktyID, "title" => "Akcie a zľavy", "url" => "akcie-a-zlavy"]);
    $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => 0, "title" => "Blog", "url" => "blogy"]);
    $websiteMenuItemModel->insertRow(["id_menu" => $idOffset + 1, "id_parent" => 0, "title" => "Kontakt", "url" => "kontakt"]);

    // web - stranky

    $websiteCommonPanels[$domainName] = [
      "header" => [ "plugin" => "WAI/Common/Header" ],
      "navigation" => [ "plugin" => "WAI/Common/Navigation", "settings" => [ "menuId" => $idOffset + 1, "homepageUrl" => "uvod", "showCategories" => true, ] ],
      "footer" => [ 
        "plugin" => "WAI/Common/Footer", 
        "settings" => [ 
          "mainMenuId" => $idOffset + 1, 
          "secondaryMenuId" => $idOffset + 3, 
          "mainMenuTitle" => "Stránky",
          "secondaryMenuTitle" => "Vaša firma",
          "showContactAddress" => 0,
          "showContactEmail" => 1,
          "showContactPhoneNumber" => 1,
          "contactTitle" => "Kontaktujte nás",
          "showPayments" => 1,
          "showSocialMedia" => 1,
          "showSecondaryMenu" => 1,
          "showMainMenu" => 1,
          "showBlogs" => 1,
          "Newsletter" => 1,
          "blogsTitle" => "Najnovšie blogy"
        ] 
      ],
    ];

    $webPages = [
      "{$domainName}|uvod|WithoutSidebar|Úvod" => [
        "section_1" => ["WAI/Misc/Slideshow", ["speed" => 1000]],
        "section_2" => [
          "WAI/SimpleContent/OneColumn",
          [
            "heading" => "Vitajte",
            "headingLevel" => 1,
            "content" => file_get_contents(__DIR__."/../SampleData/PageTexts/lorem-ipsum-1.html"),
          ],
        ],
        "section_3" => ["WAI/SimpleContent/H2", ["heading" => "Odporúčame pre vás"]],
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
            "column1Content" => file_get_contents(__DIR__."/../SampleData/PageTexts/lorem-ipsum-1.html"),
            "column1Width" => 4,
            "column2Content" => file_get_contents(__DIR__."/../SampleData/PageTexts/lorem-ipsum-2.html"),
            "column2Width" => 8,
            "column2CSSClasses" => "text-right",
          ],
        ],
        "section_6" => ["WAI/SimpleContent/H2", ["heading" => "Zľava"]],
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
            "column1Content" => file_get_contents(__DIR__."/../SampleData/PageTexts/lorem-ipsum-2.html"),
            "column1Width" => 8,
            "column2Content" => file_get_contents(__DIR__."/../SampleData/PageTexts/lorem-ipsum-1.html"),
            "column2Width" => 4,
            "column2CSSClasses" => "text-right",
          ],
        ]
      ],
      "{$domainName}|o-nas|WithoutSidebar|O nás" => [
        "section_1" => [
          "WAI/SimpleContent/OneColumn",
          [
            "heading" => "O nás",
            "content" => file_get_contents(__DIR__."/../SampleData/PageTexts/o-nas.html"),
          ]
        ],
        "section_2" => [
          "WAI/SimpleContent/OneColumn",
          [
            "heading" => "Vitajte",
            "content" => file_get_contents(__DIR__."/../SampleData/PageTexts/o-nas.html"),
          ]
        ],
      ],
      "{$domainName}|kontakt|WithoutSidebar|Kontakt" => [
        "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
        "section_2" => [
          "WAI/SimpleContent/OneColumn",
          [
            "heading" => "",
            "content" => file_get_contents(__DIR__."/../SampleData/PageTexts/kontakt_sk.html"),
          ]
        ],
      ],

      // Product catalog pages
      "{$domainName}|produkty|WithLeftSidebar|Katalóg produktov" => [
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
      "{$domainName}|akcie-a-zlavy|WithoutSidebar|Akcie a zľavy" => [
        "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
        "section_2" => ["WAI/SimpleContent/H2", ["heading" => "Zľava"]],
        "section_3" => [
          "WAI/Product/FilteredList",
          [
            "filterType" => "on_sale",
            "layout" => "tiles",
            "product_count" => 99,
          ],
        ],
        "section_4" => ["WAI/SimpleContent/H2", ["heading" => "Výpredaj"]],
        "section_5" => [
          "WAI/Product/FilteredList",
          [
            "filterType" => "sale_out",
            "layout" => "tiles",
            "product_count" => 99,
          ],
        ],
      ],
      "{$domainName}||WithoutSidebar|Detail produktu" => [
        "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
        "section_2" => ["WAI/Product/Detail", ["show_similar_products" => 1, "show_accessories" => 1, "showAuthor" => 1]],
      ],

      // Shopping cart, checkout and order confirmation
      "{$domainName}|kosik|WithoutSidebar|Nákupný košík" => [
        "section_1" => "WAI/Order/CartOverview",
      ],
      "{$domainName}|objednat|WithoutSidebar|Vytvorenie objednávky" => [
        "section_1" => "WAI/Order/Checkout",
      ],
      "{$domainName}||WithoutSidebar|Potvrdenie objednávky" => [
        "section_1" => "WAI/Order/Confirmation"
      ],

      // My account pages
      "{$domainName}|prihlasit-sa|WithoutSidebar|Môj účet - prihlásenie" => [
        "section_1" => ["WAI/Customer/Login", ["showPrivacyTerms" => 1, "privacyTermsUrl" => "privacy-terms"]],
      ],
      "{$domainName}|moj-ucet|WithoutSidebar|Môj účet" => [
        "section_1" => "WAI/Customer/Home",
      ],
      "{$domainName}|moj-ucet/objednavky|WithoutSidebar|Môj účet - objednávky" => [
        "section_1" => "WAI/Customer/OrderList",
      ],
      "{$domainName}|zabudnute-heslo|WithoutSidebar|Môj účet - resetovanie hesla" => [
        "section_1" => "WAI/Customer/ForgotPassword"
      ],
      "{$domainName}|registracia|WithoutSidebar|Môj účet - registrácia" => [
        "section_1" => ["WAI/Customer/Registration", ["showPrivacyTerms" => 1, "privacyTermsUrl" => "privacy-terms"]]
      ],
      "{$domainName}|potvrdenie-registracie|WithoutSidebar|Môj účet - potvrdenie registrácie" => [
        "section_1" => "WAI/Customer/RegistrationConfirmation"
      ],
      "{$domainName}||WithoutSidebar|Môj účet - validácia registrácie" => [
        "section_1" => "WAI/Customer/ValidationConfirmation"
      ],

      // Blogs
      "{$domainName}|blogy|WithLeftSidebar|Blogy" => [
        "sidebar" => ["WAI/Blog/Sidebar", ["showRecent" => 1, "showArchive" => 1, "showAdvertising" => 1]],
        "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
        "section_2" => ["WAI/Blog/Catalog", ['itemsPerPage' => 3, "showAuthor" => 1]],
      ],
      "{$domainName}||WithLeftSidebar|Blog" => [
        "sidebar" => ["WAI/Blog/Sidebar", ["showRecent" => 1, "showArchive" => 1, "showAdvertising" => 1]],
        "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
        "section_2" => "WAI/Blog/Detail",
      ],

      // Miscelaneous pages
      "{$domainName}|hladat|WithoutSidebar|Hľadať" => [
        "section_1" => [
          "WAI/Misc/WebsiteSearch",
          [
            "heading" => "Hľadať",
            "numberOfResults" => 10,
            "searchInProducts" => "name_lang,brief_lang,description_lang",
            "searchInProductCategories" => "name_lang",
            "searchInBlogs" => "name,content",
          ]
        ],
      ],
      "{$domainName}|ochrana-osobnych-udajov|WithoutSidebar|Zásady ochrany osobných údajov" => [
        "section_1" => [
          "WAI/SimpleContent/OneColumn",
          [
            "heading" => "Hello",
            "content" => file_get_contents(__DIR__."/SampleData/PageTexts/o-nas.html"),
          ]
        ]
      ],
      "{$domainName}|novinky|WithLeftSidebar|Novinky" => [
        "sidebar" => ["WAI/News", ["contentType" => "sidebar"]],
        "section_1" => ["WAI/News", ["contentType" => "listOrDetail"]],
      ],
    ];

    foreach ($webPages as $webPageData => $webPagePanels) {
      list($tmpDomain, $tmpUrl, $tmpLayout, $tmpTitle) = explode("|", $webPageData);
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
        "domain" => $tmpDomain,
        "name" => $tmpTitle,
        "seo_title" => $tmpTitle,
        "seo_description" => $tmpTitle,
        "url" => $tmpUrl,
        "publish_always" => 1,
        "content_structure" => json_encode([
          "layout" => $tmpLayout,
          "panels" => array_merge($websiteCommonPanels[$tmpDomain], $tmpPanels),
        ]),
      ]);
    }

    // if ($domainIndex === 1) {
    //   $websiteWebRedirectModel->insertRow([
    //     "domain" => $domainName,
    //     "from_url" => "",
    //     "to_url" => "//".$_SERVER['HTTP_HOST'].REWRITE_BASE.$domainSlug."/uvod",
    //     "type" => 302,
    //   ]);
    // }

    $websiteWebRedirectModel->insertRow([
      "domain" => $domainName,
      "from_url" => "",
      "to_url" => "//".$_SERVER['HTTP_HOST'].REWRITE_BASE.$domainSlug."/uvod",
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
              "after_order_confirmation_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/orderBody_sk.html"),
              "after_registration_SUBJECT" => "{$domainName} - Overte Vašu emailovú adresu",
              "after_registration_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/registrationBody_sk.html"),
              "forgot_password_SUBJECT" => "{$domainName} - Obnovenie hesla",
              "forgot_password_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/forgotPasswordBody_sk.html")
            ],
          ],
        ],
        // "plugins" => [
        //   "WAI/Export/MoneyS3" => [
        //     "outputFileProducts" => "tmp/money_s3_products.xml",
        //     "outputFileOrders" => "tmp/money_s3_orders.xml",
        //   ],
        // ],
      ]
    ]);

    $themeObject->onAfterInstall();

  }
}