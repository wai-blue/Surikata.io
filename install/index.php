<html>
<head>
  <title>Surikata.io Installer</title>
  <link rel='shortcut icon' href='../src/Surikata/Core/Assets/images/Surikata_logo_farebne_znak.png'>
  <style>
    * { font-family: verdana; font-size: 10pt; }
    body { background: #EEEEEE; }
    h1 { color: #224abe; font-size: 16pt; }
    h2 { color: #224abe; font-size: 12pt; }

    table { border: 1px solid #F0F0F0; }
    table tr:nth-child(even) td { background: #F0F0F0; }
    table td { padding: 2px; }

    label { display: block; padding: 2px; }
    label:hover { background: #224abe; color: white; cursor: pointer; }

    .btn { color: #224abe; background: white; cursor: pointer; border: 1px solid #224abe; padding: 1em; margin: 1em 0; }
    .btn:hover { color: white; background: #224abe; }

    a.btn { display: inline-block; text-decoration: none; }

    .content { width: 820px; margin: auto; background: white; padding: 1em; }
    .logo { width: 100px; margin: auto; }

    #log {
      background: #2d2d2d;
      font-family: courier;
      color: white;
      padding: 1em;
      font-size: 9pt;
      margin-top: 1em;
    }
  </style>
</head>
<body>
  <div class='content'>
    <img class='logo' src='../src/Surikata/Core/Assets/images/Surikata_logo_farebne_znak.png'>
    <h1>Surikata.io Installer</h1>

<?php

$installationStart = microtime(TRUE);

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

$availableLanguages = [];
foreach (@scandir(__DIR__."/languages") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableLanguages[] = $file;
  }
}

$availableSlideshowImageSets = [];
foreach (@scandir(__DIR__."/SampleData/images/slideshow") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableSlideshowImageSets[] = $file;
  }
}

$languageToInstall = $_GET['language_to_install'];
$slideshowImageSet = $_GET['slideshow_image_set'];

$randomProductsCount = $_GET['random_products_count'] ?? 50;
if ($randomProductsCount > 5000) $randomProductsCount = 5000;

$partsToInstall = [];
if (($_GET['product-catalog'] ?? "") == "yes") $partsToInstall[] = "product-catalog";
if (($_GET['customers'] ?? "") == "yes") $partsToInstall[] = "customers";
if (($_GET['orders'] ?? "") == "yes") $partsToInstall[] = "orders";

$themeName = $_GET['theme'] ?? "";
if (!in_array($themeName, $availableThemes)) {
  $themeName = reset($availableThemes);
}

if (count($partsToInstall) == 0) {

  $languageSelectOptions = "";
  foreach ($availableLanguages as $availableLanguage) {
    $languageSelectOptions .= "
      <option value='{$availableLanguage}'>{$availableLanguage}</option>
    ";
  }

  $slideshowImageSetSelectOptions = "";
  foreach ($availableSlideshowImageSets as $availableSlideshowImageSet) {
    $slideshowImageSetSelectOptions .= "
      <option value='{$availableSlideshowImageSet}'>{$availableSlideshowImageSet}</option>
    ";
  }

  $themeSelectOptions = "";
  foreach ($availableThemes as $availableTheme) {
    $themeSelectOptions .= "
      <option value='{$availableTheme}'>{$availableTheme}</option>
    ";
  }

  echo "
    <form action='' method='GET'>
      <p>
        Whitch parts do you want to install?
      </p>
      <table>
        <tr>
          <td><input type='checkbox' name='core' checked disabled></td>
          <td>Surikata Core</td>
        </tr>
        <tr>
          <td><input type='checkbox' name='website-content' checked disabled></td>
          <td>Website sitemap and basic content</td>
        </tr>
        <tr>
          <td><input type='checkbox' name='product-catalog' id='product-catalog' value='yes' checked></td>
          <td><label for='product-catalog'>Sample product product catalog</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='customers' id='customers' value='yes'></td>
          <td><label for='customers'>Sample set of customers (each customer will get a password '0000')</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='orders' id='orders' value='yes'></td>
          <td><label for='orders'>Sample set of orders</label></td>
        </tr>
      </table>
      <p>
        Select a language for the website content:
      </p>
      <select name='language_to_install'>
        {$languageSelectOptions}
      </select>
      <p>
        Select an image set for the homepage slideshow:
      </p>
      <select name='slideshow_image_set'>
        {$slideshowImageSetSelectOptions}
      </select>
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

    $themeObject = $adminPanel->widgets['Website']->themes[$themeName];

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
    $productPriceModel = new \ADIOS\Widgets\Products\Models\ProductPrice($adminPanel);
    $shoppingCartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($adminPanel);
    $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($adminPanel);
    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($adminPanel);
    $websiteMenuModel = new \ADIOS\Widgets\Website\Models\WebMenu($adminPanel);
    $websiteMenuItemModel = new \ADIOS\Widgets\Website\Models\WebMenuItem($adminPanel);
    $websiteWebPageModel = new \ADIOS\Widgets\Website\Models\WebPage($adminPanel);
    $websiteWebRedirectModel = new \ADIOS\Widgets\Website\Models\WebRedirect($adminPanel);
    $unitModel = new \ADIOS\Widgets\Settings\Models\Unit($adminPanel);
    $translationModel = new \ADIOS\Widgets\Website\Models\Translation($adminPanel);
    $newsModel = new \ADIOS\Plugins\WAI\News\Models\News($adminPanel);

    $slideshowModel = new \ADIOS\Plugins\WAI\Misc\Slideshow\Models\HomepageSlideshow($adminPanel);
    $blogCatalogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($adminPanel);
    $blogTagModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag($adminPanel);
    $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($adminPanel);

    $deliveryServiceModel = new \ADIOS\Widgets\Shipping\Models\DeliveryService($adminPanel);
    $shippingCountryModel = new \ADIOS\Widgets\Shipping\Models\Country($adminPanel);
    $paymentServiceModel = new \ADIOS\Widgets\Shipping\Models\PaymentService($adminPanel);
    $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($adminPanel);
    $shipmentPriceModel = new \ADIOS\Widgets\Shipping\Models\ShipmentPrice($adminPanel);

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: product-catalog
    
    if (in_array("product-catalog", $partsToInstall)) {

      $shippingCountryModel->insertRow(["id" => 1, "name" => "Slovakia", "flag" => NULL, "is_enabled" => TRUE]);

      $deliveryServiceModel->insertRow(["id" => 1, "name" => "UPS", "description" => "Fast & Reliable Shipping", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => "WAI/Delivery/UPS"]);
      $deliveryServiceModel->insertRow(["id" => 2, "name" => "DPD", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => "WAI/Delivery/DPD"]);
      $deliveryServiceModel->insertRow(["id" => 3, "name" => "Slovenská pošta", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => ""]);
      $deliveryServiceModel->insertRow(["id" => 4, "name" => "Packeta", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => ""]);

      $paymentServiceModel->insertRow(["id" => 1, "name" => "Tatra banka", "description" => "", "logo" => "tatrabanka.jpg", "is_enabled" => TRUE, "connected_plugin" => "WAI/Payment/Tatrabanka"]);
      $paymentServiceModel->insertRow(["id" => 2, "name" => "CardPay", "description" => "", "logo" => "cardpay.jpg", "is_enabled" => TRUE, "connected_plugin" => "WAI/Payment/Card"]);
      $paymentServiceModel->insertRow(["id" => 3, "name" => "Payment on delivery", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => ""]);

      // UPS
      $shipmentModel->insertRow(["id" => 1, "name" => "UPS Tatra banka", "description" => "", "id_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 2, "name" => "UPS CardPay", "description" => "", "id_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 2, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 3, "name" => "UPS Cash on delivery", "description" => "", "id_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 1, "id_shipment" => 1, "name" => "11", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 50, "shipment_price_calculation_method" => 1, "shipment_price" => 3.25]);
      $shipmentPriceModel->insertRow(["id" => 2, "id_shipment" => 2, "name" => "12", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 50, "shipment_price_calculation_method" => 1, "shipment_price" => 3.95]);
      $shipmentPriceModel->insertRow(["id" => 3, "id_shipment" => 3, "name" => "13", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 50, "shipment_price_calculation_method" => 1, "shipment_price" => 4.99]);

      // UPS FREE
      $shipmentPriceModel->insertRow(["id" => 9, "id_shipment" => 1, "name" => "14", "weight_from" => 0, "weight_to" => 0, "price_from" => 50, "price_to" => 100000, "shipment_price_calculation_method" => 1, "shipment_price" => 0.00]);
      $shipmentPriceModel->insertRow(["id" => 10, "id_shipment" => 2, "name" => "15", "weight_from" => 0, "weight_to" => 0, "price_from" => 50, "price_to" => 100000, "shipment_price_calculation_method" => 1, "shipment_price" => 0.00]);
      $shipmentPriceModel->insertRow(["id" => 11, "id_shipment" => 3, "name" => "16", "weight_from" => 0, "weight_to" => 0, "price_from" => 50, "price_to" => 100000, "shipment_price_calculation_method" => 1, "shipment_price" => 0.75]);

      // Slovenska posta
      $shipmentModel->insertRow(["id" => 4, "name" => "Slovenská pošta Tatra banka", "description" => "", "id_country" => 1, "id_delivery_service" => 3, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 5, "name" => "Slovenská pošta Cash on delivery", "description" => "", "id_country" => 1, "id_delivery_service" => 3, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 4, "id_shipment" => 4, "name" => "21", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "shipment_price_calculation_method" => 1, "shipment_price" => 4.25]);
      $shipmentPriceModel->insertRow(["id" => 5, "id_shipment" => 5, "name" => "22", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "shipment_price_calculation_method" => 1, "shipment_price" => 6.00]);

      // Packeta
      $shipmentModel->insertRow(["id" => 6, "name" => "Packeta Cash on delivery", "description" => "", "id_country" => 1, "id_delivery_service" => 4, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 6, "id_shipment" => 6, "name" => "31", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "shipment_price_calculation_method" => 1, "shipment_price" => 2.00]);

      // DPD
      $shipmentModel->insertRow(["id" => 7, "name" => "DPD Tatra banka", "description" => "", "id_country" => 1, "id_delivery_service" => 2, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 8, "name" => "DPD CardPay", "description" => "", "id_country" => 1, "id_delivery_service" => 2, "id_payment_service" => 2, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 7, "id_shipment" => 7, "name" => "41", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "shipment_price_calculation_method" => 1, "shipment_price" => 3.35]);
      $shipmentPriceModel->insertRow(["id" => 8, "id_shipment" => 8, "name" => "42", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "shipment_price_calculation_method" => 1, "shipment_price" => 3.99]);
      

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

      $serviceModel->insertRow(["name_lang_1" => "Return within 30 days", "name_lang_2" => "Vrátenie do 30 dní"]);
      $serviceModel->insertRow(["name_lang_1" => "Satisfaction guaranteed", "name_lang_2" => "Garancia spokojnosti"]);
      $serviceModel->insertRow(["name_lang_1" => "Free shipping", "name_lang_2" => "Doprava zdarma"]);
      $serviceModel->insertRow(["name_lang_1" => "Possible exchange", "name_lang_2" => "Možná výmena"]);

      // products - categories

      $productCategoryModel->insertRow([
        "id" => 1,
        "id_parent" => 0,
        "code" => "CatA",
        "name_lang_1" => "Category_A (lng-1)",
        "name_lang_2" => "Category_A (lng-2)",
        "name_lang_3" => "Category_A (lng-3)"
      ]);
      $productCategoryModel->insertRow([
        "id" => 2,
        "id_parent" => 0,
        "code" => "CatB",
        "name_lang_1" => "Category_B (lng-1)",
        "name_lang_2" => "Category_B (lng-2)",
        "name_lang_3" => "Category_B (lng-3)"
      ]);
      $productCategoryModel->insertRow([
        "id" => 3,
        "id_parent" => 0,
        "code" => "CatC",
        "name_lang_1" => "Category_C (lng-1)",
        "name_lang_2" => "Category_C (lng-2)",
        "name_lang_3" => "Category_C (lng-3)"
      ]);

      $productCategoryModel->insertRow([
        "id" => 4,
        "id_parent" => 1,
        "code" => "CatAA",
        "name_lang_1" => "Category_A_A (lng-1)",
        "name_lang_2" => "Category_A_A (lng-2)",
        "name_lang_3" => "Category_A_A (lng-3)"
      ]);
      $productCategoryModel->insertRow([
        "id" => 5,
        "id_parent" => 1,
        "code" => "CatAB",
        "name_lang_1" => "Category_A_B (lng-1)",
        "name_lang_2" => "Category_A_B (lng-2)",
        "name_lang_3" => "Category_A_B (lng-3)"
      ]);
      $productCategoryModel->insertRow([
        "id" => 6,
        "id_parent" => 1,
        "code" => "CatAC",
        "name_lang_1" => "Category_A_C (lng-1)",
        "name_lang_2" => "Category_A_C (lng-2)",
        "name_lang_3" => "Category_A_C (lng-3)"
      ]);
      $productCategoryModel->insertRow([
        "id" => 7,
        "id_parent" => 2,
        "code" => "CatBA",
        "name_lang_1" => "Category_B_A (lng-1)",
        "name_lang_2" => "Category_B_A (lng-2)",
        "name_lang_3" => "Category_B_A (lng-3)"
      ]);
      
      // produkty - vlastnosti produktov, ciselnik
      $productFeatureModel->insertRow(["id" => 1, "order_index" => 1, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Lange", "name_lang_2" => "Dĺžka", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 2, "order_index" => 2, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Breite", "name_lang_2" => "Šírka", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 3, "order_index" => 3, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Hohe", "name_lang_2" => "Výška", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 4, "order_index" => 4, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 5,     "name_lang_1" => "Achsen", "name_lang_2" => "Nápravy", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 5, "order_index" => 5, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Gesamtgewicht", "name_lang_2" => "Celková hmotnosť", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 6, "order_index" => 6, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Nutzlast ca.", "name_lang_2" => "Užitočné zaťaženie", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 7, "order_index" => 7, "value_type" => 2, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Rader", "name_lang_2" => "Kolesá", "id_measurement_unit" => 1]);

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

    }

      ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // PART: website content

      mkdir("../upload/blogs/");
      mkdir("../upload/products/");
      mkdir("../upload/slideshow/");

      for ($i = 1; $i <= 7; $i++) {
        copy(
          __DIR__."/SampleData/images/category_{$i}.png",
          "{$adminPanel->config['files_dir']}/blogs/category_{$i}.png",
        );
      }
    for ($i = 1; $i <= 10; $i++) {
        copy(
          __DIR__."/SampleData/images/product_{$i}.jpg",
          "{$adminPanel->config['files_dir']}/products/product_{$i}.jpg",
        );
      }
      for ($i = 1; $i <= 3; $i++) {
        copy(
          __DIR__."/SampleData/images/slideshow/{$slideshowImageSet}/{$i}.jpg",
          "{$adminPanel->config['files_dir']}/slideshow/{$i}.jpg",
        );
      }

      copy(
        __DIR__."/SampleData/images/your-logo.png",
        "{$adminPanel->config['files_dir']}/your-logo.png",
      );

      require(__DIR__."/languages/{$languageToInstall}");

      $adminPanel->saveConfig(
        [
          "model" => $productModel->name,
          "search" => base64_encode(json_encode([
            "is_recommended" => 1,
          ]))
        ],
        "UI/Table/savedSearches/Products/Recommended products/"
      );


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: customers
    
    if (in_array("customers", $partsToInstall)) {
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

    if (in_array("orders", $partsToInstall)) {
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
            "domain"            => "EN",
            "general_terms_and_conditions"  => 1,
            "gdpr_consent"                  => 1,
            "confirmation_time" => $orderConfirmationTime,
          ],
          $customerUID
        );

        if (rand(0, 1) == 1) {
          $idInvoice = $orderModel->issueInvoce($idOrder, TRUE);
        }

      }
    }

    $themeObject->onAfterInstall();

  } catch (\Exception $e) {
    echo "
      <h2 style='color:red'>Error</h2>
      <div style='color:red'>
        ".get_class($e).": ".$e->getMessage()."
      </div>
    ";
    var_dump($e->getTrace());
  }

  $infos = $adminPanel->console->getInfos();

  echo "
    <h2>Done in ".round((microtime(true) - $installationStart), 2)." seconds.</h2>
    <div style='color:green;margin-bottom:1em'>
      ✓ Congratulations. You have successfuly installed your eCommerce project.
    </div>
    <div style='color:orange;margin-bottom:1em'>
      ⚠ WARNING: You should delete the <i>install</i> folder now.
    </div>
    <table>
      <tr><td>Theme</td><td>{$themeName}</td></tr>
      <tr><td>Content language</td><td>{$languageToInstall}</td></tr>
      <tr><td>Slideshow image set</td><td>{$slideshowImageSet}</td></tr>
      <tr><td>Sample set of products</td><td>".(in_array("product-catalog", $partsToInstall) ? "yes" : "no")."</td></tr>
      <tr><td>Random products count</td><td>{$randomProductsCount}</td></tr>
      <tr><td>Sample set of customers</td><td>".(in_array("customers", $partsToInstall) ? "yes" : "no")."</td></tr>
      <tr><td>Sample set of orders</td><td>".(in_array("orders", $partsToInstall) ? "yes" : "no")."</td></tr>
    </table>
    <a href='../admin' class='btn' target=_blank>Open administration panel</a><br/>
    Login: administrator<br/>
    Password: administrator<br/>
    <br/>
    <a href='..' class='btn' target=_blank>Go to your e-shop</a>
    <br/>
    <h2>Installation log</h2>
    <a
      href='javascript:void(0)'
      onclick='
        document.getElementById(\"log\").style.display = \"block\";
        this.style.display = \"none\";
      '
    >Show installation log</a>
    <div id='log' style='display:none'>".$adminPanel->console->convertLogsToHtml($infos, TRUE)."</div>
  ";

  $warnings = $adminPanel->console->getWarnings();
  if (count($warnings) > 0) {
    echo "<h2>Warnings</h2>";
    echo "<div style='color:orange'>".$adminPanel->console->convertLogsToHtml($warnings)."</div>";
  }

  $errors = $adminPanel->console->getErrors();
  if (count($errors) > 0) {
    echo "<h2>Errors</h2>";
    echo "<div style='color:red'>".$adminPanel->console->convertLogsToHtml($errors)."</div>";
  }
}

?>
  </div>
</body>
</html>