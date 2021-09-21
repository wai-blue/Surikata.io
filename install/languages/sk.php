<?php

  function ___webPageSimpleText($url, $title) {
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


  // Blogs
  $blogCatalogModel->insertRow(["name" => "Ako vznikol vesmír?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_7.png", "created_at" => date("Y-m-d"), "id_user" => 1]);
  $blogCatalogModel->insertRow(["name" => "Blog?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_3.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")),  "id_user" => 2]);
  $blogCatalogModel->insertRow(["name" => "Lorem Ipsum", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_6.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")), "id_user" => 1]);
  $blogCatalogModel->insertRow(["name" => "Ahoj Blog", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_1.png", "created_at" => date("Y-m-d", strtotime("8.8.2000")), "id_user" => 3]);

  // Blogs tags
  $blogTagModel->insertRow(["name" => "Žltý", "description" => "Žltá farba"]);
  $blogTagModel->insertRow(["name" => "Modrý", "description" => "Modrá farba"]);
  $blogTagModel->insertRow(["name" => "Červený", "description" => "Červená farba"]);

  // Blogs tags assignment
  $blogTagAssignmentModel->insertRow(["id_tag" => 1, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 3, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 2]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 1, "id_blog" => 3]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 3, "id_blog" => 4]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 4]);

  foreach ($configEnv["domains"] as $domain => $domainInfo) {
    // Slideshow

    $domainName = $domainInfo['name'];

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

    $websiteMenuModel->insertRow(["id" => 1, "domain" => $domainName, "name" => "Menu v hlavičke ({$domainName})"]);
    $websiteMenuModel->insertRow(["id" => 2, "domain" => $domainName, "name" => "Menu v päte stránky ({$domainName})"]);

    // web - menu items
    $tmpHomepageID = $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Úvod", "url" => "uvod"]);
    $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => $tmpHomepageID, "title" => "O nás", "url" => "o-nas"]);
    $tmpProduktyID = $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Produkty", "url" => "produkty"]);
    $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => $tmpProduktyID, "title" => "Akcie a zľavy", "url" => "akcie-a-zlavy"]);
    $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Blog", "url" => "blogy"]);
    //$tmpHomepageID = $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Prihlásiť sa", "url" => "prihlasit-sa"]);
    //$websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => $tmpHomepageID, "title" => "Registrovať sa", "url" => "registracia"]);
    $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Kontakt", "url" => "kontakt"]);

    // web - stranky

    $websiteCommonPanels[$domainName] = [
      "header" => [ "plugin" => "WAI/Common/Header" ],
      "navigation" => [ "plugin" => "WAI/Common/Navigation", "settings" => [ "menuId" => 1, "homepageUrl" => "home", "showCategories" => true, ] ],
      "footer" => [ 
        "plugin" => "WAI/Common/Footer", 
        "settings" => [ 
          "mainMenuId" => 1, 
          "secondaryMenuId" => 3, 
          "mainMenuTitle" => "Pages", 
          "secondaryMenuTitle" => "Generally",
          "showContactAddress" => 0,
          "showContactEmail" => 1,
          "showContactPhoneNumber" => 1,
          "contactTitle" => "Contact Us",
          "showPayments" => 1,
          "showSocialMedia" => 1,
          "showSecondaryMenu" => 1,
          "showMainMenu" => 1,
          "showBlogs" => 1,
          "Newsletter" => 1,
          "blogsTitle" => "Newest blogs"
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
        "sidebar" => ["WAI/Product/Filter", ["showProductCategories" => 1, "layout" => "sidebar", "showProductCategories" => 1, "showBrands" => 1]],
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

    $websiteWebRedirectModel->insertRow([
      "domain" => $domainName,
      "from_url" => "",
      "to_url" => REWRITE_BASE."uvod",
      "type" => 301
    ]);

    $adminPanel->widgets["Website"]->rebuildSitemap($domainName);

    // nastavenia webu

    $adminPanel->saveConfig([
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
                "headerMenuID" => 1,
                "footerMenuID" => 2,
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
              "after_order_confirmation_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/orderBody.html"),
              "after_registration_SUBJECT" => "{$domainName} - Overte Vašu emailovú adresu",
              "after_registration_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/registrationBody.html"),
              "forgot_password_SUBJECT" => "{$domainName} - Obnovenie hesla",
              "forgot_password_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/forgotPasswordBody.html")
            ],
          ],
        ],
        "plugins" => [
          "WAI/Export/MoneyS3" => [
            "outputFileProducts" => "tmp/money_s3_products.xml",
            "outputFileOrders" => "tmp/money_s3_orders.xml",
          ],
        ],
      ]
    ]);
  }
