<?php

  // Blogs
  $blogCatalogModel->insertRow(["name" => "Where does it come from?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_7.png", "created_at" => date("Y-m-d"), "id_user" => 1]);
  $blogCatalogModel->insertRow(["name" => "Where can I get some?", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_3.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")),  "id_user" => 2]);
  $blogCatalogModel->insertRow(["name" => "Lorem Ipsum", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex2.html"), "image" => "blogs/category_6.png", "created_at" => date("Y-m-d", strtotime("19.5.2000")), "id_user" => 1]);
  $blogCatalogModel->insertRow(["name" => "Hello Blog", "content" => file_get_contents(__DIR__."/SampleData/PageTexts/kontakty.html"), "perex" => file_get_contents(__DIR__."/SampleData/PageTexts/blogs/perex1.html"), "image" => "blogs/category_1.png", "created_at" => date("Y-m-d", strtotime("8.8.2000")), "id_user" => 3]);

  // Blogs tags
  $blogTagModel->insertRow(["name" => "Yellow", "description" => "Yellow color"]);
  $blogTagModel->insertRow(["name" => "Blue", "description" => "Blue color"]);
  $blogTagModel->insertRow(["name" => "Boat", "description" => "Boat"]);

  // Blogs tags assignment
  $blogTagAssignmentModel->insertRow(["id_tag" => 1, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 3, "id_blog" => 1]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 2]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 1, "id_blog" => 3]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 3, "id_blog" => 4]);
  $blogTagAssignmentModel->insertRow(["id_tag" => 2, "id_blog" => 4]);

  // Slideshow
  $slideshowModel->insertRow(["heading" => "Welcome", "description" => "Get up to 50% off Today Only!", "image" => "slideshow/1.jpg",]);
  $slideshowModel->insertRow(["heading" => "Sales", "description" => "50% off in all products", "image" => "slideshow/2.jpg"]);
  $slideshowModel->insertRow(["heading" => "Black Friday", "description" => "Taking your Viewing Experience to Next Level", "image" => "slideshow/3.jpg"]);

  // novinky

  $newsModel->insertRow([
    "title" => "FIRST NEW",
    "content" => "Very first new",
    "perex" => "Short description for First New",
    "domain" => "sk",
    "image" => "",
    "show_from" => "20.6.2021",
  ]);

  $newsModel->insertRow([
    "title" => "SECOND NEW",
    "content" => "Second and the last new",
    "perex" => "Short description for Second New",
    "domain" => "sk",
    "image" => "",
    "show_from" => "22.6.2021",
  ]);

  // web - menu

  $websiteMenuModel->insertRow(["id" => 1, "domain" => "EN", "name" => "Header Menu (EN)"]);
  $websiteMenuModel->insertRow(["id" => 2, "domain" => "EN", "name" => "Footer Menu (EN)"]);

  // web - menu items - EN
  $tmpHomepageID = $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Home", "url" => "home"]);
  $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => $tmpHomepageID, "title" => "About us", "url" => "about-us"]);
  $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Products", "url" => "products"]);
  $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Blogs", "url" => "blogs"]);
  $tmpHomepageID = $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Login", "url" => "login"]);
  $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => $tmpHomepageID, "title" => "Register", "url" => "register"]);
  $websiteMenuItemModel->insertRow(["id_menu" => 1, "id_parent" => 0, "title" => "Contact", "url" => "contact"]);

  // web - stranky

  $websiteCommonPanels["EN"] = [
    "header" => [ "plugin" => "WAI/Common/Header" ],
    "navigation" => [ "plugin" => "WAI/Common/Navigation", "settings" => [ "menuId" => 1, "homepageUrl" => "home", ] ],
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

  function ___webPageSimpleText($url, $title) {
    return [
      "section_1" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => $title,
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/{$url}.html"),
        ]
      ],
    ];
  }

  $webPages = [
    "EN|home|WithoutSidebar|Home" => [
      "section_1" => ["WAI/Misc/Slideshow", ["speed" => 1000]],
      "section_2" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => "Welcome",
          "headingLevel" => 1,
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/lorem-ipsum-1.html"),
        ],
      ],
      "section_3" => [
        "WAI/Product/FilteredList",
        [
          "filterType" => "recommended",
          "layout" => "tiles",
          "product_count" => 6,
        ],
      ],
      "section_4" => [
        "WAI/SimpleContent/TwoColumns",
        [
          "column1Content" => file_get_contents(__DIR__."/SampleData/PageTexts/lorem-ipsum-1.html"),
          "column1Width" => 4,
          "column2Content" => file_get_contents(__DIR__."/SampleData/PageTexts/lorem-ipsum-2.html"),
          "column2Width" => 8,
          "column2CSSClasses" => "text-right",
        ],
      ],
      "section_5" => [
        "WAI/Product/FilteredList",
        [
          "filterType" => "on_sale",
          "layout" => "tiles",
          "product_count" => 6,
        ],
      ],
      "section_6" => [
        "WAI/SimpleContent/TwoColumns",
        [
          "column1Content" => file_get_contents(__DIR__."/SampleData/PageTexts/lorem-ipsum-2.html"),
          "column1Width" => 8,
          "column2Content" => file_get_contents(__DIR__."/SampleData/PageTexts/lorem-ipsum-1.html"),
          "column2Width" => 4,
          "column2CSSClasses" => "text-right",
        ],
      ]
    ],
    "EN|about-us|WithoutSidebar|About us" => [
      "section_1" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => "About us",
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/about-us.html"),
        ]
      ],
      "section_2" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => "Hello",
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/about-us.html"),
        ]
      ],
    ],
    "EN|contact|WithoutSidebar|Contact" => [
      "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
      "section_2" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => "",
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/contact.html"),
        ]
      ],
    ],

    // Product catalog pages
    "EN|products|WithLeftSidebar|Products - Catalog" => [
      "sidebar" => ["WAI/Product/Filter", ["showProductCategories" => 1, "layout" => "sidebar", "showProductCategories" => 1, "showBrands" => 1]],
      "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
      "section_2" => ["WAI/Product/Catalog", ["defaultItemsPerPage" => 6]],
    ],
    "EN||WithoutSidebar|Products - Detail" => [
      "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
      "section_2" => ["WAI/Product/Detail", ["show_similar_products" => 1, "show_accessories" => 1, "showAuthor" => 1]],
    ],

    // Shopping cart, checkout and order confirmation
    "EN|cart|WithoutSidebar|Shopping cart" => [
      "section_1" => "WAI/Order/CartOverview",
    ],
    "EN|checkout|WithoutSidebar|Checkout" => [
      "section_1" => "WAI/Order/Checkout",
    ],
    "EN||WithoutSidebar|Order - Confirmation" => [
      "section_1" => "WAI/Order/Confirmation"
    ],

    // My account pages
    "EN|login|WithoutSidebar|My account - Login" => [
      "section_1" => ["WAI/Customer/Login", ["showPrivacyTerms" => 1, "privacyTermsUrl" => "privacy-terms"]],
    ],
    "EN|my-account|WithoutSidebar|My account - Home" => [
      "section_1" => "WAI/Customer/Home",
    ],
    "EN|my-account/orders|WithoutSidebar|My account - Orders" => [
      "section_1" => "WAI/Customer/OrderList",
    ],
    "EN|reset-password|WithoutSidebar|My account - Reset password" => [
      "section_1" => "WAI/Customer/ForgotPassword"
    ],
    "EN|registration|WithoutSidebar|My account - Registration" => [
      "section_1" => ["WAI/Customer/Registration", ["showPrivacyTerms" => 1, "privacyTermsUrl" => "privacy-terms"]]
    ],
    "EN|registration-confirm|WithoutSidebar|My account - Registration - Confirmation" => [
      "section_1" => "WAI/Customer/RegistrationConfirmation"
    ],
    "EN||WithoutSidebar|My account - Registration - Validation" => [
      "section_1" => "WAI/Customer/ValidationConfirmation"
    ],

    // Blogs
    "EN|blogs|WithLeftSidebar|Blogs" => [
      "sidebar" => ["WAI/Blog/Sidebar", ["showRecent" => 1, "showArchive" => 1, "showAdvertising" => 1]],
      "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
      "section_2" => ["WAI/Blog/Catalog", ['itemsPerPage' => 3, "showAuthor" => 1]],
    ],
    "EN||WithLeftSidebar|Blog" => [
      "sidebar" => ["WAI/Blog/Sidebar", ["showRecent" => 1, "showArchive" => 1, "showAdvertising" => 1]],
      "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
      "section_2" => "WAI/Blog/Detail",
    ],

    // Miscelaneous pages
    "EN|search|WithoutSidebar|Search" => [
      "section_1" => [
        "WAI/Misc/WebsiteSearch",
        [
          "heading" => "Search",
          "numberOfResults" => 10,
          "searchInProducts" => "name_lang,brief_lang,description_lang",
          "searchInProductCategories" => "name_lang",
          "searchInBlogs" => "name,content",
        ]
      ],
    ],
    "EN|privacy-terms|WithoutSidebar|Privacy policy" => [
      "section_1" => [
        "WAI/SimpleContent/OneColumn",
        [
          "heading" => "Hello",
          "content" => file_get_contents(__DIR__."/SampleData/PageTexts/about-us.html"),
        ]
      ]
    ],
    "EN|news|WithLeftSidebar|News" => [
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
      "url" => $tmpUrl,
      "publish_always" => 1,
      "content_structure" => json_encode([
        "layout" => $tmpLayout,
        "panels" => array_merge($websiteCommonPanels[$tmpDomain], $tmpPanels),
      ]),
    ]);
  }

  $websiteWebRedirectModel->insertRow([
    "domain" => "EN",
    "from_url" => "",
    "to_url" => REWRITE_BASE."home",
    "type" => 301
  ]);

  $adminPanel->widgets["Website"]->rebuildSitemap("EN");






  // nastavenia webu

  foreach ($configEnv["domains"] as $domain => $domainInfo) {
    $domainName = $domainInfo['name'];

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
              "after_order_confirmation_SUBJECT" => "{$domainName} - Your Order nr. {% number %}",
              "after_order_confirmation_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/orderBody.html"),
              "after_registration_SUBJECT" => "{$domainName} - Verify Your Email Address",
              "after_registration_BODY" => file_get_contents(__DIR__."/../SampleData/PageTexts/emails/registrationBody.html"),
              "forgot_password_SUBJECT" => "{$domainName} - Password recovery",
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
