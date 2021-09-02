<html>
<head>
  <title>Surikata E-shop Installer</title>
  <style>
    * { font-family: verdana; font-size: 10pt; }
    body { background: #EEEEEE; }
    h1 { color: #224abe; font-size: 16pt; }
    h2 { color: #224abe; font-size: 12pt; }

    table { border: 1px solid #F0F0F0; width: 100%; }
    table tr:nth-child(even) td { background: #F0F0F0; }
    table td { padding: 2px; }

    label { display: block; padding: 2px; }
    label:hover { background: #224abe; color: white; cursor: pointer; }

    .btn { color: #224abe; background: white; cursor: pointer; border: 1px solid #224abe; padding: 1em; width: 100%; }
    .btn:hover { color: white; background: #224abe; }

    .content { width: 600px; margin: auto; background: white; padding: 1em; }
    .logo { width: 100px; margin: auto; }
  </style>
</head>
<body>
  <div class='content'>
    <img class='logo' src='../src/Surikata/Core/Assets/images/Surikata_logo_farebne_znak.png'>
    <h1>Surikata E-shop Installer</h1>

<?php

include("RandomGenerator.php");

function _loadCsvIntoArray($file, $separator = ',', $enclosure = '#') {
  $lines = [];

  $file = fopen($file, 'r');
  while (($line = fgetcsv($file, 0, $separator, $enclosure)) !== FALSE) {
    $lines[] = $line;
  }
  fclose($file);

  return $lines;
}

set_time_limit(60*10);

if (!is_file("../vendor/autoload.php")) {
  echo "
    <div style='color:red'>
      Sorry, it looks like you did not run 'composer install'.
    </div>
  ";
  exit();
}

if (!is_file("../ConfigEnv.php")) {
  echo "
    <div style='color:red'>
      Sorry, it looks like you do not have ConfigEnv.php configured.
    </div>
  ";
  exit();
}

require("../Init.php");

session_start();

$availableThemes = [];
foreach (@scandir(__DIR__."/../src/Themes") as $dir) {
  if (
    !in_array($dir, [".", ".."])
    && is_file(__DIR__."/../src/Themes/{$dir}/Main.php")
  ) {
    $availableThemes[] = $dir;
  }
}


$randomProductsCount = $_GET['random_products_count'] ?? 50;
if ($randomProductsCount > 5000) $randomProductsCount = 5000;

$parts = [];
if (($_GET['product-catalog'] ?? "") == "yes") $parts[] = "product-catalog";
if (($_GET['customers'] ?? "") == "yes") $parts[] = "customers";
if (($_GET['orders'] ?? "") == "yes") $parts[] = "orders";

$theme = $_GET['theme'] ?? "";
if (!in_array($theme, $availableThemes)) {
  $theme = reset($availableThemes);
}

if (count($parts) == 0) {

  $themeSelectOptions = "";
  foreach ($availableThemes as $availableTheme) {
    $themeSelectOptions .= "
      <option value='{$availableTheme}'>{$availableTheme}</option>
    ";
  }

  echo "
    <form action='' method='GET'>
      <p>
        Select whitch parts do you want to install:
      </p>
      <table>
        <tr>
          <td><input type='checkbox' name='core' checked disabled></td>
          <td>Surikata Core</td>
        </tr>
        <tr>
          <td><input type='checkbox' name='product-catalog' id='product-catalog' value='yes' checked></td>
          <td><label for='product-catalog'>Sample product product catalog</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='customers' id='customers' value='yes'></td>
          <td><label for='customers'>Sample set of customers</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='orders' id='orders' value='yes'></td>
          <td><label for='orders'>Sample set of orders</label></td>
        </tr>
      </table>
      <p>
        Number of random products to be generated:
      </p>
      <select name='random_products_count'>
        <option value='0'>0</option>
        <option value='10' selected>10</option>
        <option value='100'>100</option>
        <option value='1000'>1000</option>
        <option value='5000'>5000</option>
      </select>
      <p>
        Select a theme to use:
      </p>
      <select name='theme'>
        {$themeSelectOptions}
      </select>
      <p>
        Select a color scheme:<br/>
      </p>
      <div style='color:#888888'>[To be done]</div>
      <br/>
      <input type='submit' class='btn' value='Hurray! Create Surikata e-shop now.' />
    </form>
  ";
} else {

  try {

    $websiteRenderer = new \Surikata\Core\Web\Loader($websiteRendererConfig);
    $adminPanel = new \Surikata\Core\AdminPanel\Loader($adminPanelConfig, ADIOS_MODE_FULL, $websiteRenderer);
    $adminPanel->install();
    $adminPanel->installDefaultUsers();
    $adminPanel->createMissingFolders();

    $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($adminPanel);
    $customerCategoryModel = new \ADIOS\Widgets\Customers\Models\CustomerCategory($adminPanel);
    $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($adminPanel);
    $customerWishlistModel = new \ADIOS\Widgets\Customers\Models\CustomerWishlist($adminPanel);
    $customerWatchdogModel = new \ADIOS\Widgets\Customers\Models\CustomerWatchdog($adminPanel);
    $invoiceNumericSeriesModel = new \ADIOS\Widgets\Finances\Models\InvoiceNumericSeries($adminPanel);
    $supplierModel = new \ADIOS\Widgets\Products\Models\Supplier($adminPanel);
    $brandModel = new \ADIOS\Widgets\Products\Models\Brand($adminPanel);
    $serviceModel = new \ADIOS\Widgets\Products\Models\Service($adminPanel);
    $productServiceAssigmentModel = new \ADIOS\Widgets\Products\Models\ProductServiceAssignment($adminPanel);
    $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($adminPanel);
    $productModel = new \ADIOS\Widgets\Products\Models\Product($adminPanel);
    $productGalleryModel = new \ADIOS\Widgets\Products\Models\ProductGallery($adminPanel);
    $productRelatedModel = new \ADIOS\Widgets\Products\Models\ProductRelated($adminPanel);
    $productAccessoryModel = new \ADIOS\Widgets\Products\Models\ProductAccessory($adminPanel);
    $productFeatureModel = new \ADIOS\Widgets\Products\Models\ProductFeature($adminPanel);
    $productFeatureAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductFeatureAssignment($adminPanel);
    $productPriceModel = new \ADIOS\Widgets\Prices\Models\ProductPrice($adminPanel);
    $shoppingCartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($adminPanel);
    $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($adminPanel);
    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($adminPanel);
    $websiteMenuModel = new \ADIOS\Widgets\Website\Models\WebMenu($adminPanel);
    $websiteMenuItemModel = new \ADIOS\Widgets\Website\Models\WebMenuItem($adminPanel);
    $websiteWebPageModel = new \ADIOS\Widgets\Website\Models\WebPage($adminPanel);
    $websiteWebRedirectModel = new \ADIOS\Widgets\Website\Models\WebRedirect($adminPanel);
    $unitModel = new \ADIOS\Widgets\Settings\Models\Unit($adminPanel);
    $translationModel = new \ADIOS\Widgets\Settings\Models\Translation($adminPanel);
    $newsModel = new \ADIOS\Plugins\WAI\News\Models\News($adminPanel);

    $slideshowModel = new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\UvodnaSlideshow($adminPanel);
    $blogCatalogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($adminPanel);
    $blogTagModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag($adminPanel);
    $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($adminPanel);

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: product-catalog
    
    if (in_array("product-catalog", $parts)) {

      // merne jednotky
      $unitModel->insertRow(["id" => 1, "unit" => "N/A", "name" => "no unit", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 2, "unit" => "mm", "name" => "milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 3, "unit" => "cm", "name" => "centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 4, "unit" => "m", "name" => "meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 22, "unit" => "km", "name" => "kilometers", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 5, "unit" => "ml", "name" => "mililitres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 6, "unit" => "dl", "name" => "decilitres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 7, "unit" => "l", "name" => "litres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 8, "unit" => "mg", "name" => "miligramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 9, "unit" => "g", "name" => "gramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 10, "unit" => "kg", "name" => "kilogramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 11, "unit" => "t", "name" => "tonnes", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 12, "unit" => "mm2", "name" => "square milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 13, "unit" => "cm2", "name" => "square centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 14, "unit" => "m2", "name" => "square meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 15, "unit" => "mm3", "name" => "cubic milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 16, "unit" => "cm3", "name" => "cubic centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 17, "unit" => "m3", "name" => "cubic meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 18, "unit" => "btl", "name" => "bottles", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 19, "unit" => "pcs", "name" => "pieces", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 20, "unit" => "pkg", "name" => "packages", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 21, "unit" => "cnt", "name" => "containers", "is_for_products" => TRUE, "is_for_features" => TRUE]);

      // produkty - dodavatelia
      $supplierModel->insertRow(["name" => "Baumax"]);
      $supplierModel->insertRow(["name" => "Amazon"]);
      $supplierModel->insertRow(["name" => "Adidas"]);
      $supplierModel->insertRow(["name" => "Bosch"]);
      $supplierModel->insertRow(["name" => "SpaceX"]);

      $suppliersCount = $supplierModel->get()->count();

      // produkty - vyrobcovia
      $brandModel->insertRow(["name" => "Mercedes"]);
      $brandModel->insertRow(["name" => "Hyunday"]);
      $brandModel->insertRow(["name" => "Lenovo"]);
      $brandModel->insertRow(["name" => "Yves Rocher"]);
      $brandModel->insertRow(["name" => "Milsy"]);
      $brandModel->insertRow(["name" => "Tommy Hilgifer"]);
      $brandModel->insertRow(["name" => "Kia"]);
      $brandModel->insertRow(["name" => "ConnectIT"]);
      $brandModel->insertRow(["name" => "Samsung"]);
      $brandModel->insertRow(["name" => "IKAR"]);
      $brandModel->insertRow(["name" => "IKEA"]);

      $brandsCount = $brandModel->get()->count();

      // produkty - sluzby

      $serviceModel->insertRow(["name_lang_1" => "Vrátenie do 30 dní"]);
      $serviceModel->insertRow(["name_lang_1" => "Garancia spokojnosti"]);
      $serviceModel->insertRow(["name_lang_1" => "Doprava zdarma"]);
      $serviceModel->insertRow(["name_lang_1" => "Možná výmena"]);

      // products - categories

      $productCategoryModel->insertRow([ "id" => 1, "id_parent" => 0, "code" => "Category_A", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_A"]);
      $productCategoryModel->insertRow([ "id" => 2, "id_parent" => 0, "code" => "Category_B", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_B"]);
      $productCategoryModel->insertRow([ "id" => 3, "id_parent" => 0, "code" => "Category_C", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_C"]);

      $productCategoryModel->insertRow([ "id" => 4, "id_parent" => 1, "code" => "Category_A_A", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_A_A"]);
      $productCategoryModel->insertRow([ "id" => 5, "id_parent" => 1, "code" => "Category_A_B", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_A_B"]);
      $productCategoryModel->insertRow([ "id" => 6, "id_parent" => 1, "code" => "Category_A_C", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_A_C"]);
      $productCategoryModel->insertRow([ "id" => 7, "id_parent" => 2, "code" => "Category_B_B", "obrazok" => "products/daltec-trailer.png", "name_lang_1" => "Category_B_B"]);
      
      // produkty - vlastnosti produktov, ciselnik
      $productFeatureModel->insertRow(["id" => 1, "order_index" => 1, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Lange", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 2, "order_index" => 2, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Breite", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 3, "order_index" => 3, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Hohe", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 4, "order_index" => 4, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 5,     "name_lang_1" => "Achsen", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 5, "order_index" => 5, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Gesamtgewicht", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 6, "order_index" => 6, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Nutzlast ca.", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 7, "order_index" => 7, "value_type" => 2, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Rader", "id_measurement_unit" => 1]);

      $productFeaturesCount = $productFeatureModel->get()->count();

      // produkty - produkty
      RandomGenerator::generateRandomProducts(
        $randomProductsCount,
        $productModel,
        $productFeatureAssignmentModel
      );

      $products = $productModel->get()->toArray();
      $productsCount = $productModel->get()->count();

      // produkty - galeria produktov
      $adminPanel->db->start_transaction();

      foreach ($products as $product) {
        for ($i = 1; $i <= 8; $i++) {
          $productGalleryModel->insertRow([
            "id_product" => $product['id'],
            "image" => "products/product_" . rand(1, 10) . ".jpg",
          ]);
        }
      }

      foreach ($products as $product) {
        $productServiceAssigmentModel->insertRow([
          "id_product" => $product['id'],
          "id_service" => rand(0, 1),
        ]);
        $productServiceAssigmentModel->insertRow([
          "id_product" => $product['id'],
          "id_service" => rand(2, 3),
        ]);
      }

      $adminPanel->db->commit();
      
      // produkty - podobne protuky
      $adminPanel->db->start_transaction();

      foreach ($products as $product) {
        for ($i = 1; $i <= 8; $i++) {
          do {
            $idRelated = rand(1, count($products));
          } while ($idRelated == $product['id']);

          $productRelatedModel->insertRow([
            "id_product" => $product['id'],
            "id_related" => $idRelated,
          ]);
        }
      }

      $adminPanel->db->commit();

      // produkty - prislusenstvo k produktom
      $adminPanel->db->start_transaction();

      foreach ($products as $product) {
        for ($i = 1; $i <= 8; $i++) {
          do {
            $idAccessory = rand(1, count($products));
          } while ($idAccessory == $product['id']);

          $productAccessoryModel->insertRow([
            "id_product" => $product['id'],
            "id_accessory" => $idAccessory,
          ]);
        }
      }

      // produkty - vlastnosti produktov, priradenie
      $adminPanel->db->start_transaction();

      foreach ($products as $product) {
        for ($i = 1; $i <= 3; $i++) {
          $productFeatureAssignmentModel->insertRow([
            "id_product" => $product['id'],
            "id_feature" => rand(1, $productFeaturesCount),
            "value_number" => rand(900, 9999) / rand(900, 1000),
          ]);
        }
      }

      $adminPanel->db->commit();

      // nakupny cennik

      for ($i = 1; $i <= $productsCount; $i++) {
        $productPriceModel->insertRandomRow(["id_product" => $i]);
      }

      mkdir("../upload/blogs/");
      mkdir("../upload/products/");

      for ($i = 1; $i <= 7;$i++) {
        copy(
          __DIR__."/SampleData/images/category_{$i}.png",
          "{$adminPanel->config['files_dir']}/blogs/category_{$i}.png",
        );
        copy(
          __DIR__."/SampleData/images/product_{$i}.jpg",
          "{$adminPanel->config['files_dir']}/products/product_{$i}.jpg",
        );
      }
      for ($i = 1; $i <= 3;$i++) {
        copy(
          __DIR__."/SampleData/images/books_{$i}.jpg",
          "{$adminPanel->config['files_dir']}/books_{$i}.jpg",
        );
      }
      for ($i = 1; $i <= 4;$i++) {
        copy(
          __DIR__."/SampleData/images/product_{$i}.png",
          "{$adminPanel->config['files_dir']}/products/product_{$i}.png",
        );
      }

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
      $slideshowModel->insertRow(["heading" => "Welcome", "description" => "Get up to 50% off Today Only!", "image" => "slideshow/books_1.jpg",]);
      $slideshowModel->insertRow(["heading" => "Sales", "description" => "50% off in all products", "image" => "slideshow/books_2.jpg"]);
      $slideshowModel->insertRow(["heading" => "Black Friday", "description" => "Taking your Viewing Experience to Next Level", "image" => "slideshow/books_3.jpg"]);

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
              "filterType" => "discounted",
              "layout" => "tiles",
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
              "heading" => "Vitajte",
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

        // Product catalog pages
        "EN|products|WithLeftSidebar|Products - Catalog" => [
          "sidebar" => ["WAI/Product/Filter", ["showProductCategories" => 1, "layout" => "sidebar", "showProductCategories" => 1, "show_brands" => 1]],
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => ["WAI/Product/Catalog", ["defaultItemsPerPage" => 6]],
        ],
        "EN||WithoutSidebar|Products - Detail" => [
          "section_1" => ["WAI/Common/Breadcrumb", ["showHomePage" => 1]],
          "section_2" => ["WAI/Product/Detail", ["zobrazit_podobne_produkty" => 1, "show_accessories" => 1, "showAuthor" => 1]],
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

      copy(
        __DIR__."/SampleData/images/surikata.png",
        "{$adminPanel->config['files_dir']}/surikata.png",
      );

      // nastavenia webu

      $adminPanel->saveConfig([
        "settings" => [
          "web" => [
            "EN" => [
              "profile" => [
                "slogan" => "My online store",
                "contactPhoneNumber" => "+421 111 222 333",
                "contactEmail" => "info@{$_SERVER['HTTP_HOST']}",
                "logo" => "surikata.png",
                "urlFacebook" => "www.google.com",
                "urlTwitter" => "www.google.com",
                "urlYouTube" => "www.google.com",
                "urlInstagram" => "www.google.com"
              ],
              "design" => [
                "theme" => $theme,
                "themeMainColor" => "#17C3B2",
                "themeSecondColor" => "#222222",
                "themeThirdColor" => "#FE6D73",
                "themeGreyColor" => "#888888",
                "themeLightGreyColor" => "#f5f5f5",

                "bodyBgColor" => "#ffffff",
                "bodyTextColor" => "#333333",
                "bodyLinkColor" => "#17C3B2",
                "bodyHeadingColor" => "#333333",

                "headerBgColor" => "#000000",
                "headerTextColor" => "#333333",
                "headerLinkColor" => "#17C3B2",
                "headerHeadingColor" => "#ffffff",

                "footerBgColor" => "#222222",
                "footerTextColor" => "#f8f1e4",
                "footerLinkColor" => "#17C3B2",
                "footerHeadingColor" => "#ffffff",

                "custom_css" => "li.slideshow-basic {
                  background: rgb(29,6,7);
                  background: linear-gradient(180deg, rgba(29,6,7,1) 0%, rgba(29,6,7,0.75) 15%, rgba(73,18,18,0.6) 35%, rgba(156,36,38,0) 100%);
                  }
                  .rslides {
                  background: #000;
                  }",
                "headerMenuID" => 1,
                "footerMenuID" => 2,
              ],
              "legalDisclaimers" => [
                "generalTerms" => "Bienvenue. VOP!",
                "privacyPolicy" => "Bienvenue. OOU!",
                "returnPolicy" => "Bienvenue. RP!",
              ],
            ],
          ],
          "emails" => [
            "EN" => [
              "signature" => "<p>Surikata - <a href='www.wai.sk' target='_blank'>WAI.sk</a></p>",
              "after_order_confirmation_SUBJECT" => "Surikata - order n. {% number %}",
              "after_order_confirmation_BODY" => file_get_contents(__DIR__."/SampleData/PageTexts/emails/orderBody.html"),
              "after_registration_SUBJECT" => "Surikata - Verify Email Address",
              "after_registration_BODY" => file_get_contents(__DIR__."/SampleData/PageTexts/emails/registrationBody.html"),
              "forgot_password_SUBJECT" => "Surikata - Password recovery",
              "forgot_password_BODY" => file_get_contents(__DIR__."/SampleData/PageTexts/emails/forgotPasswordBody.html")
            ]
          ],
          "plugins" => [
            "WAI/Export/MoneyS3" => [
              "outputFileProducts" => "tmp/money_s3_products.xml",
              "outputFileOrders" => "tmp/money_s3_orders.xml",
            ],
          ],
        ]
      ]);

      $adminPanel->saveConfig(
        [
          "model" => $productModel->name,
          "search" => base64_encode(json_encode([
            "is_recommended" => 1,
          ]))
        ],
        "UI/Table/savedSearches/Products/Recommended products/"
      );
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: customers
    
    if (in_array("customers", $parts)) {
      // customer categories

      $customerCategories = [
        ["id" => 1, "code" => "G", "name" => "Golden"],
        ["id" => 2, "code" => "S", "name" => "Silver"],
        ["id" => 3, "code" => "B", "name" => "Bronze"],
      ];

      foreach ($customerCategories as $category) {
        $customerCategoryModel->insertRow($category);
      }

      // customers
      // .csv file generated with the help of https://www.fakeaddressgenerator.com
      $customers = _loadCsvIntoArray(__DIR__."/SampleData/Customers.csv");

      $cnt = 1;
      for ($i = 0; $i < 10; $i++) {
        foreach ($customers as $customer) {
          $tmpPassword = password_hash("0000", PASSWORD_DEFAULT);

          $idCustomer = $customerModel->insertRow([
            "id_category" => $customer[0],
            "email" => strtolower("{$customer[1]}.{$customer[2]}.{$cnt}@example.com"),
            "given_name" => $customer[1],
            "family_name" => $cnt." ".$customer[2],
            "password" => $tmpPassword,
            "password_1" => $tmpPassword,
            "password_2" => $tmpPassword,
            "is_validated" => 1,
          ]); 

          // address
          $customerAddressModel->insertRow([
            "id_customer" => $idCustomer,
            "del_given_name" => $customer[1],
            "del_family_name" => $customer[2],
            "del_street_1" => $customer[3],
            "del_city" => $customer[4],
            "del_zip" => $customer[5],
            "del_country" => $customer[6],
            // "email" => strtolower("{$customer[1]}.{$customer[2]}@example.com"),
            "email" => "example@email.com",
            "inv_given_name" => $customer[1],
            "inv_family_name" => $customer[2],
            "inv_street_1" => $customer[3],
            "inv_city" => $customer[4],
            "inv_zip" => $customer[5],
            "inv_country" => $customer[6],
            "phone_number" => "+1 123 456 789",
          ]);

          // wishlist
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 1]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 2]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 3]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 4]);

          // watchdog
          $customerWatchdogModel->insertRow(["id_customer" => $idCustomer, "id_product" => 5]);
          $customerWatchdogModel->insertRow(["id_customer" => $idCustomer, "id_product" => 8]);
        }

        $cnt++;
      }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: orders

    if (in_array("orders", $parts)) {
      // invoice numerical series
      $invoiceNumericSeriesModel->insertRow(["id" => 1, "name" => "Regular invoices", "pattern" => "YYMMDDNNNN"]);
      $invoiceNumericSeriesModel->insertRow(["id" => 2, "name" => "Advance invoices", "pattern" => "YYMMDDNNNN"]);

      $customersCount = $customerModel->get()->count();
      $productsCount = $productModel->get()->count();

      $orderModel->disableNotifications = TRUE;

      for ($i = 1; $i <= 40; $i++) {
        $idCustomer = rand(1, $customersCount);
        $customer = $customerModel->getById($idCustomer);
        $customerUID = $customerModel->assignCustomerUID($idCustomer);
        $address = reset($customer['ADDRESSES']);

        for ($j = 1; $j < rand(3, 5); $j++) {
          $shoppingCartModel->addProductToCart($customerUID, rand(1, $productsCount), rand(1, 10));
        }

        $orderConfirmationTime = date("Y-m-d H:i:s", strtotime("-".rand(0, 365)." days"));

        $idOrder = $orderModel->placeOrder(
          [
            "id_customer"       => $idCustomer,
            "del_given_name"    => $address['del_given_name'],
            "del_family_name"   => $address['del_family_name'],
            "del_company_name"  => $address['del_company_name'],
            "del_street_1"      => $address['del_street_1'],
            "del_street_2"      => $address['del_street_2'],
            "del_floor"         => $address['del_floor'],
            "del_city"          => $address['del_city'],
            "del_zip"           => $address['del_zip'],
            "del_region"        => $address['del_region"'],
            "del_country"       => $address['del_country'],
            "inv_given_name"    => $address['inv_given_name'],
            "inv_family_name"   => $address['inv_family_name'],
            "inv_company_name"  => $address['inv_company_name'],
            "inv_street_1"      => $address['inv_street_1'],
            "inv_street_2"      => $address['inv_street_2'],
            "inv_floor"         => $address['inv_floor'],
            "inv_city"          => $address['inv_city'],
            "inv_zip"           => $address['inv_zip'],
            "inv_region"        => $address['inv_region'],
            "inv_country"       => $address['inv_country'],
            "phone_number"      => $address['phone_number'],
            "email"             => $address['email'],
            "confirmation_time" => $orderConfirmationTime,
          ],
          $customerUID
        );

        if (rand(0, 1) == 1) {
          $idInvoice = $orderModel->issueInvoce($idOrder, TRUE);
        }

      }
    }

  } catch (\Exception $e) {
    echo "
      <h2 style='color:red'>Error</h2>
      <div style='color:red'>
        ".get_class($e).": ".$e->getMessage()."
      </div>
    ";
    var_dump($e->getTrace());
  }

}

?>
  </div>
</body>
</html>