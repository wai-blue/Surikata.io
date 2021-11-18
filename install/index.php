<?php

function _echo($msg) {
  if (php_sapi_name() !== 'cli') {
    echo $msg;
  }
}

_echo("
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
");

$installationStart = microtime(TRUE);
$rewriteBaseIsCorrect = ($_GET['rewrite_base_is_correct'] ?? "") == "1";

include("RandomProductsGenerator.php");
include("WebsiteContentGenerator.php");

$configEnvDomainLanguagesPHP = '$configEnv["domainLanguages"] = [1 => "English", 2 => "Slovensky", 3 => "Česky"];';
file_put_contents(__DIR__."/../ConfigEnvDomains.php", '<?php '.$configEnvDomainLanguagesPHP);

function _loadCsvIntoArray($file, $separator = ',', $enclosure = '#') {
  $lines = [];

  $file = fopen($file, 'r');
  while (($line = fgetcsv($file, 0, $separator, $enclosure)) !== FALSE) {
    $lines[] = $line;
  }
  fclose($file);

  return $lines;
}

set_time_limit(0);

if (!is_file(__DIR__."/../vendor/autoload.php")) {
  _echo("
    <div style='color:red'>
      Sorry, it looks like you did not run 'composer install'.<br/>
      <br/>
      Install required libraries:
      <ul>
        <li>run <i>composer install</i> in project's root folder</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

if (!is_file(__DIR__."/../ConfigEnv.php")) {
  _echo("
    <div style='color:red'>
      Sorry, it looks like you do not have your ConfigEnv.php configured.<br/>
      <br/>
      Configure your environment:
      <ul>
        <li>copy <i>ConfigEnv.php.tmp</i> to <i>ConfigEnv.php</i></li>
        <li>modify it based on your environment</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

require(__DIR__."/../Init.php");

if (empty(REWRITE_BASE) || empty(DB_LOGIN) || empty(DB_NAME)) {
  _echo("
    <div style='color:red'>
      Sorry, it looks like you did not configure necessary parameters.<br/>
      <br/>
      Check following configurations in your ConfigEnv.php file:
      <ul>
        <li>REWRITE_BASE</li>
        <li>DB_LOGIN</li>
        <li>DB_NAME</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

if (!$rewriteBaseIsCorrect) {
  $expectedRewriteBase = $_SERVER['REQUEST_URI'];
  $expectedRewriteBase = str_replace("install/", "", $expectedRewriteBase);
  $expectedRewriteBase = str_replace("index.php", "", $expectedRewriteBase);
  if (REWRITE_BASE != $expectedRewriteBase) {
    _echo("
      <div style='color:orange'>
        We think that your REWRITE_BASE is not configured properly.<br/>
        <br/>
        REWRITE_BASE that you have configured: <b>".REWRITE_BASE."</b><br/>
        REWRITE_BASE that we think is correct: <b>{$expectedRewriteBase}</b><br/>
        <br/>
        If you are sure that you configured your REWRITE_BASE correctly,
        click on the link below.<br/>
        <br/>
        <a href='?rewrite_base_is_correct=1'>REWRITE_BASE is correctly configured, continue with installation</a>
      </div>
    ");
    exit();
  }
}

$availableThemes = [];
foreach (@scandir(__DIR__."/../src/Themes") as $dir) {
  if (
    !in_array($dir, [".", ".."])
    && is_file(__DIR__."/../src/Themes/{$dir}/Main.php")
  ) {
    $availableThemes[] = $dir;
  }
}

foreach (@scandir(__DIR__."/../prop/Themes") as $dir) {
  if (
    !in_array($dir, [".", ".."])
    && is_file(__DIR__."/../prop/Themes/{$dir}/Main.php")
  ) {
    $availableThemes[] = $dir;
  }
}

$availableThemes = array_unique($availableThemes);
sort($availableThemes);

$availableLanguages = [];
foreach (@scandir(__DIR__."/languages") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableLanguages[] = $file;
  }
}

$availableSlideshowImageSets = [];
foreach (@scandir(__DIR__."/content/images/slideshow") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableSlideshowImageSets[] = $file;
  }
}

$doInstall = ($_GET['do_install'] === "1");
// $languageToInstall = $_GET['language_to_install'];
$slideshowImageSet = $_GET['slideshow_image_set'];

$domainsToInstall = [];
for ($i = 1; $i <= 3; $i++) {
  if (!empty($_GET["domain_{$i}_description"])) {
    $domainsToInstall[$i] = [
      "name" => \ADIOS\Core\HelperFunctions::str2url($_GET["domain_{$i}_description"]),
      "description" => $_GET["domain_{$i}_description"],
      "slug" => $_GET["domain_{$i}_slug"],
      "themeName" => $_GET["domain_{$i}_theme_name"],
      "languageIndex" => $_GET["domain_{$i}_language_index"],
    ];
  }
}

$randomProductsCount = $_GET['random_products_count'] ?? 50;
if ($randomProductsCount > 100000) $randomProductsCount = 100000;

$partsToInstall = [];
if (($_GET['product-catalog'] ?? "") == "yes") $partsToInstall[] = "product-catalog";
if (($_GET['delivery-and-payment-services'] ?? "") == "yes") $partsToInstall[] = "delivery-and-payment-services";
if (($_GET['customers'] ?? "") == "yes") $partsToInstall[] = "customers";
if (($_GET['orders'] ?? "") == "yes") $partsToInstall[] = "orders";

// $themeName = $_GET['theme'] ?? "";
// if (!in_array($themeName, $availableThemes)) {
//   $themeName = reset($availableThemes);
// }

if (!$doInstall) {

  // $languageSelectOptions = "";
  // foreach ($availableLanguages as $availableLanguage) {
  //   $languageSelectOptions .= "
  //     <option value='{$availableLanguage}'>{$availableLanguage}</option>
  //   ";
  // }

  function _getDomainDescriptionInput($domainIndex, $value = "") {
    return "
      <input
        name='domain_{$domainIndex}_description'
        value='{$value}'
        style='width:300px'
      >
    ";
  }

  function _getDomainSlugInput($domainIndex, $value = "") {
    return "
      <input
        name='domain_{$domainIndex}_slug'
        value='{$value}'
        style='width:150px'
      >
    ";
  }

  function _getDomainLanguageIndexInput($domainIndex, $value = "") {
    $languages = [
      1 => "English",
      2 => "Slovensky",
      3 => "Česky",
    ];

    $html = "<select name='domain_{$domainIndex}_language_index'>";
    foreach ($languages as $languageIndex => $language) {
      $html .= "<option value='{$languageIndex}' ".($value == $languageIndex ? "selected" : "").">{$language}</option>";
    }
    $html .= "</select>";

    return $html;
  }

  function _getDomainThemeSelect($domainIndex, $availableThemes, $theme = "") {
    $html = "<select name='domain_{$domainIndex}_theme_name'>";
    foreach ($availableThemes as $availableTheme) {
      $html .= "<option value='{$availableTheme}' ".($theme == $availableTheme ? "selected" : "").">{$availableTheme}</option>";
    }
    $html .= "</select>";

    return $html;
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

  _echo("
    <form action='' method='GET'>
      <input type='hidden' name='do_install' value='1' />
      <input type='hidden' name='rewrite_base_is_correct' value='1' />

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
          <td>
            <label for='product-catalog'>Sample product product catalog</label>
          </td>
          <td>
            <select name='random_products_count'>
              <option value='10' selected>10 random products</option>
              <option value='100'>100 random products</option>
              <option value='1000'>1000 random products</option>
              <option value='5000'>5000 random products</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><input type='checkbox' name='customers' id='customers' value='yes' checked></td>
          <td>
            <label for='customers'>Sample set of customers</label>
          </td>
          <td>
            Each customer will get a password '0000'.
          </td>
        </tr>
        <tr>
          <td><input type='checkbox' name='delivery-and-payment-services' id='delivery-and-payment-services' value='yes' checked></td>
          <td><label for='delivery-and-payment-services'>Sample delivery and payment services</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='orders' id='orders' value='yes' checked></td>
          <td><label for='orders'>Sample set of orders</label></td>
        </tr>
      </table>
      <p>
        Configure domains to install:
      </p>
      <table>
        <tr>
          <td><b>Slug</b></td>
          <td><b>Domain description</b></td>
          <td><b>Language</b></td>
          <td><b>Theme</b></td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(1, "hello-world")."</td>
          <td>"._getDomainDescriptionInput(1, "Developer`s Hello World example")."</td>
          <td>"._getDomainLanguageIndexInput(1, 1)."</td>
          <td>"._getDomainThemeSelect(1, $availableThemes, "HelloWorld")."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(2, "en")."</td>
          <td>"._getDomainDescriptionInput(2, "English version")."</td>
          <td>"._getDomainLanguageIndexInput(2, 1)."</td>
          <td>"._getDomainThemeSelect(2, $availableThemes)."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(3, "sk")."</td>
          <td>"._getDomainDescriptionInput(3, "Slovenská verzia")."</td>
          <td>"._getDomainLanguageIndexInput(3, 2)."</td>
          <td>"._getDomainThemeSelect(3, $availableThemes)."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(4, "")."</td>
          <td>"._getDomainDescriptionInput(4, "")."</td>
          <td>"._getDomainLanguageIndexInput(4, 3)."</td>
          <td>"._getDomainThemeSelect(4, $availableThemes)."</td>
        </tr>
      </table>
      <p style='color:#888888'>
        It is also possible to create more domains using the same language and with different design or
        product catalog filtered for a specific brand.
      </p>
      <!-- <select name='language_to_install'>
        {$languageSelectOptions}
      </select> -->
      <p>
        Select an image set for the homepage slideshow:
      </p>
      <select name='slideshow_image_set'>
        {$slideshowImageSetSelectOptions}
      </select>
      <!-- <p>
        Select a theme to use:
      </p>
      <select name='theme'>
        {$themeSelectOptions}
      </select> -->
      <!-- <p>
        Select a color scheme:<br/>
      </p>
      <div style='color:#888888'>[To be done]</div> -->
      <br/>
      <input type='submit' class='btn' value='Hurray! Create Surikata e-shop now.' />
    </form>
  ");
} else {

  try {
    

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialization

    $websiteRenderer = new \MyEcommerceProject\Web($websiteRendererConfig);
    $adminPanel = new \MyEcommerceProject\AdminPanel($adminPanelConfig, ADIOS_MODE_FULL, $websiteRenderer);
    $adminPanel->console->cliEchoEnabled = TRUE;

    $adminPanel->console->info("Installation started.");

    $adminPanel->createMissingFolders();

    $adminPanel->install();
    $adminPanel->installDefaultUsers();

    $adminPanel->console->info("Default users created.");

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
    $productStockStateModel = new \ADIOS\Widgets\Products\Models\ProductStockState($adminPanel);
    $shoppingCartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($adminPanel);
    $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($adminPanel);
    $orderModel = new \ADIOS\Widgets\Orders\Models\Order($adminPanel);
    $orderTagModel = new \ADIOS\Widgets\Orders\Models\OrderTag($adminPanel);
    $orderTagAssignmentModel = new \ADIOS\Widgets\Orders\Models\OrderTagAssignment($adminPanel);
    $unitModel = new \ADIOS\Widgets\Settings\Models\Unit($adminPanel);
    $translationModel = new \ADIOS\Widgets\Website\Models\WebTranslation($adminPanel);

    $deliveryServiceModel = new \ADIOS\Widgets\Shipping\Models\DeliveryService($adminPanel);
    $destinationCountryModel = new \ADIOS\Widgets\Shipping\Models\DestinationCountry($adminPanel);
    $paymentServiceModel = new \ADIOS\Widgets\Shipping\Models\PaymentService($adminPanel);
    $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($adminPanel);
    $shipmentPriceModel = new \ADIOS\Widgets\Shipping\Models\ShipmentPrice($adminPanel);

    // ConfigEnvDomains.php
    $configEnvDomainsPHP = "<?php\r\n";
    $configEnvDomainsPHP .= "\r\n";
    $configEnvDomainsPHP .= $configEnvDomainLanguagesPHP."\r\n";
    $configEnvDomainsPHP .= "\r\n";
    $configEnvDomainsPHP .= '$configEnv["domains"] = ['."\r\n";
    foreach ($domainsToInstall as $key => $domain) {
      $configEnvDomainsPHP .= "  [\r\n";
      $configEnvDomainsPHP .= "    'name' => '{$domain['name']}',\r\n";
      $configEnvDomainsPHP .= "    'description' => '{$domain['description']}',\r\n";
      $configEnvDomainsPHP .= "    'slug' => '{$domain['slug']}',\r\n";
      $configEnvDomainsPHP .= "    'rootUrl' => \$_SERVER['HTTP_HOST'].REWRITE_BASE.'{$domain['slug']}',\r\n";
      $configEnvDomainsPHP .= "    'languageIndex' => {$domain['languageIndex']},\r\n";
      $configEnvDomainsPHP .= "  ],\r\n";
    }
    $configEnvDomainsPHP .= "];\r\n";
    $configEnvDomainsPHP .= "\r\n";

    $configEnvDomainsPHP .= trim('
$re = "/^".str_replace("/", "\\/", REWRITE_BASE)."/";
$slug = reset(explode("/", preg_replace($re, "", $_SERVER["REQUEST_URI"])));

$domainToRender = reset($configEnv["domains"]);
foreach ($configEnv["domains"] as $domain) {
  if ($domain["slug"] == $slug) {
    $domainToRender = $domain;
  }
}

define("WEBSITE_DOMAIN_TO_RENDER", $domainToRender["name"]);
define("WEBSITE_REWRITE_BASE", REWRITE_BASE.$domainToRender["slug"]."/");
    ');

    file_put_contents(__DIR__."/../ConfigEnvDomains.php", $configEnvDomainsPHP);

    $adminPanel->console->info("ConfigEnvDomains.php created.");

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: delivery and payment services

    if (in_array("delivery-and-payment-services", $partsToInstall)) {

      // Destination countries

      $destinationCountryModel->insertRow(["id" => 1, "name" => "Slovakia", "flag" => NULL, "is_enabled" => TRUE]);
      $destinationCountryModel->insertRow(["id" => 2, "name" => "France", "flag" => NULL, "is_enabled" => TRUE]);
      $destinationCountryModel->insertRow(["id" => 3, "name" => "United Kingdom", "flag" => NULL, "is_enabled" => TRUE]);
      $destinationCountryModel->insertRow(["id" => 4, "name" => "U.S.", "flag" => NULL, "is_enabled" => TRUE]);

      $destinationCountries = $destinationCountryModel->getAll();

      // Delivery services

      $deliveryServiceModel->insertRow(["id" => 1, "name" => "UPS", "description" => "Fast & Reliable Shipping", "logo" => "ups.svg", "is_enabled" => TRUE, "connected_plugin" => "WAI/Delivery/UPS"]);
      $deliveryServiceModel->insertRow(["id" => 2, "name" => "DPD", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => "WAI/Delivery/DPD"]);
      $deliveryServiceModel->insertRow(["id" => 3, "name" => "Slovenská pošta", "description" => "", "logo" => "posta.svg", "is_enabled" => TRUE, "connected_plugin" => ""]);
      $deliveryServiceModel->insertRow(["id" => 4, "name" => "Packeta", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => ""]);

      $deliveryServices = $deliveryServiceModel->getAll();

      // Payment services

      $paymentServiceModel->insertRow(["id" => 1, "name" => "Tatra banka", "description" => "", "logo" => "tatrabanka.jpg", "is_enabled" => TRUE, "connected_plugin" => "WAI/Proprietary/Payment/InternetBanking/Tatrabanka"]);
      $paymentServiceModel->insertRow(["id" => 2, "name" => "CardPay", "description" => "", "logo" => "cardpay.jpg", "is_enabled" => TRUE, "connected_plugin" => "WAI/Proprietary/Payment/Card"]);
      $paymentServiceModel->insertRow(["id" => 3, "name" => "Payment on delivery", "description" => "", "logo" => "", "is_enabled" => TRUE, "connected_plugin" => ""]);

      $paymentServices = $paymentServiceModel->getAll();

      // UPS
      $shipmentModel->insertRow(["id" => 1, "name" => "UPS Tatra banka", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 2, "name" => "UPS CardPay", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 2, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 3, "name" => "UPS Cash on delivery", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 1, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow([
        "id" => 1, "id_shipment" => 1, "name" => "11",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 0, "price_to" => 50,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 3.25,
        "payment_fee" => 1.15,
      ]);
      $shipmentPriceModel->insertRow([
        "id" => 2, "id_shipment" => 2, "name" => "12",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 0, "price_to" => 50,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 3.25,
        "payment_fee" => 2.10,
      ]);
      $shipmentPriceModel->insertRow([
        "id" => 3, "id_shipment" => 3, "name" => "13",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 0, "price_to" => 50,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 3.25,
        "payment_fee" => 3.11,
      ]);

      // UPS FREE
      $shipmentPriceModel->insertRow([
        "id" => 9, "id_shipment" => 1, "name" => "14",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 50, "price_to" => 100000,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 0.00,
        "payment_fee" => 4.44,
      ]);
      $shipmentPriceModel->insertRow([
        "id" => 10, "id_shipment" => 2, "name" => "15",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 50, "price_to" => 100000,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 0.00,
        "payment_fee" => 0.00,
      ]);
      $shipmentPriceModel->insertRow([
        "id" => 11, "id_shipment" => 3, "name" => "16",
        "weight_from" => 0, "weight_to" => 0,
        "price_from" => 50, "price_to" => 100000,
        "delivery_fee_calculation_method" => 1, "delivery_fee" => 0.00,
        "payment_fee" => 0.90,
      ]);

      // Slovenska posta
      $shipmentModel->insertRow(["id" => 4, "name" => "Slovenská pošta Tatra banka", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 3, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 5, "name" => "Slovenská pošta Cash on delivery", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 3, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 4, "id_shipment" => 4, "name" => "21", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "delivery_fee_calculation_method" => 1, "delivery_fee" => 4.25]);
      $shipmentPriceModel->insertRow(["id" => 5, "id_shipment" => 5, "name" => "22", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "delivery_fee_calculation_method" => 1, "price" => 6.00]);

      // Packeta
      $shipmentModel->insertRow(["id" => 6, "name" => "Packeta Cash on delivery", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 4, "id_payment_service" => 3, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 6, "id_shipment" => 6, "name" => "31", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "delivery_fee_calculation_method" => 1, "delivery_fee" => 2.00]);

      // DPD
      $shipmentModel->insertRow(["id" => 7, "name" => "DPD Tatra banka", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 2, "id_payment_service" => 1, "is_enabled" => TRUE, "order_index" => ""]);
      $shipmentModel->insertRow(["id" => 8, "name" => "DPD CardPay", "description" => "", "id_destination_country" => 1, "id_delivery_service" => 2, "id_payment_service" => 2, "is_enabled" => TRUE, "order_index" => ""]);

      $shipmentPriceModel->insertRow(["id" => 7, "id_shipment" => 7, "name" => "41", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "delivery_fee_calculation_method" => 1, "delivery_fee" => 3.35]);
      $shipmentPriceModel->insertRow(["id" => 8, "id_shipment" => 8, "name" => "42", "weight_from" => 0, "weight_to" => 0, "price_from" => 0, "price_to" => 1000, "delivery_fee_calculation_method" => 1, "delivery_fee" => 3.99]);

      $adminPanel->console->info("Delivery and payment services installed.");
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: product-catalog

    if (in_array("product-catalog", $partsToInstall)) {

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
      $brandModel->insertRow(["name" => "Hyundai"]);
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
      $productFeatureModel->insertRow(["id" => 1, "order_index" => 1, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Length", "name_lang_2" => "Dĺžka", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 2, "order_index" => 2, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Width", "name_lang_2" => "Šírka", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 3, "order_index" => 3, "value_type" => 1, "entry_method" => 5, "min" => 1, "min" => 10000, "name_lang_1" => "Height", "name_lang_2" => "Výška", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 4, "order_index" => 4, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 5,     "name_lang_1" => "Achsen", "name_lang_2" => "Nápravy", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 5, "order_index" => 5, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Gesamtgewicht", "name_lang_2" => "Celková hmotnosť", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 6, "order_index" => 6, "value_type" => 1, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Nutzlast ca.", "name_lang_2" => "Užitočné zaťaženie", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 7, "order_index" => 7, "value_type" => 2, "entry_method" => 5, "min" => 2, "min" => 10000, "name_lang_1" => "Rader", "name_lang_2" => "Kolesá", "id_measurement_unit" => 1]);

      $productFeaturesCount = $productFeatureModel->get()->count();

      // produkty - stavy na sklade
      $productStockStateModel->insertRow(["id" => 1, "name_lang_1" => "Available in stock", "name_lang_2" => "Skladom", "name_lang_3" => "Skladom"]);
      $productStockStateModel->insertRow(["id" => 2, "name_lang_1" => "Currently unavailable", "name_lang_2" => "Nedostupné", "name_lang_3" => "Nedostupné"]);
      $productStockStateModel->insertRow(["id" => 3, "name_lang_1" => "Available upon request", "name_lang_2" => "Na otázku", "name_lang_3" => "Na otázku"]);

      // produkty - produkty
      $adminPanel->db->startTransaction();

      RandomProductsGenerator::generateRandomProducts(
        $randomProductsCount,
        $productModel,
        $productFeatureAssignmentModel
      );

      $adminPanel->db->commit();

      $products = $productModel->getAll();//get()->toArray();
      $productsCount = count($products);

      // produkty - galeria produktov
      $adminPanel->db->startTransaction();

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
      $adminPanel->db->startTransaction();

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
      $adminPanel->db->startTransaction();

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

      // nakupny cennik

      $adminPanel->db->startTransaction();

      for ($i = 1; $i <= $productsCount; $i++) {
        $productPriceModel->insertRandomRow(["id_product" => $i]);
      }

      $adminPanel->db->commit();

      $adminPanel->console->info("Product catalog installed.");
    }

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
      $customers = _loadCsvIntoArray(__DIR__."/content/Customers.csv");

      $cnt = 1;
      for ($i = 0; $i < 10; $i++) {
        foreach ($customers as $customer) {
          $tmpPassword = password_hash("0000", PASSWORD_DEFAULT);

          $idCustomer = $customerModel->insertRow([
            "id_category" => $customer[0],
            "email" => strtolower("{$customer[1]}.{$customer[2]}.{$cnt}@example.com"),
            "given_name" => $customer[1],
            "family_name" => $cnt." ".$customer[2],

            "inv_given_name" => $customer[1],
            "inv_family_name" => $customer[2],
            "inv_street_1" => $customer[3],
            "inv_city" => $customer[4],
            "inv_zip" => $customer[5],
            "inv_country" => $customer[6],

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
            "phone_number" => "+1 123 456 789",
          ]);

          // wishlist
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 1]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 2]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 3]);
          $customerWishlistModel->insertRow(["id_customer" => $idCustomer, "id_product" => 4]);

          // watchdog
          $customerWatchdogModel->insertRow(["id_customer" => $idCustomer, "id_product" => 1]);
          $customerWatchdogModel->insertRow(["id_customer" => $idCustomer, "id_product" => 2]);
        }

        $cnt++;
      }

      $adminPanel->console->info("Customers installed.");
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: orders

    if (in_array("orders", $partsToInstall)) {
      // invoice numerical series
      $invoiceNumericSeriesModel->insertRow(["id" => 1, "name" => "Regular invoices", "pattern" => "YYMMDDNNNN"]);
      $invoiceNumericSeriesModel->insertRow(["id" => 2, "name" => "Advance invoices", "pattern" => "YYMMDDNNNN"]);

      $customersCount = $customerModel->get()->count();
      $productsCount = $productModel->get()->count();

      $orderTagModel->insertRow(["tag" => "paid", "color" => "#00A500"]);
      $orderTagModel->insertRow(["tag" => "unpaid", "color" => "#AA0000"]);
      $orderTagModel->insertRow(["tag" => "good client", "color" => "#11009A"]);
      $orderTagModel->insertRow(["tag" => "bad client", "color" => "#DFDFDF"]);
      $orderTagModel->insertRow(["tag" => "discount on services", "color" => "#141414"]);

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

        $destinationCountriesIds = array_keys($destinationCountries);
        $deliveryServicesIds = array_keys($deliveryServices);
        $paymentServicesIds = array_keys($paymentServices);

        $idOrder = $orderModel->placeOrder(
          [
            "id_customer"       => $idCustomer,

            "inv_given_name"               => $customer['inv_given_name'],
            "inv_family_name"              => $customer['inv_family_name'],
            "inv_company_name"             => $customer['inv_company_name'],
            "inv_street_1"                 => $customer['inv_street_1'],
            "inv_street_2"                 => $customer['inv_street_2'],
            "inv_floor"                    => $customer['inv_floor'],
            "inv_city"                     => $customer['inv_city'],
            "inv_zip"                      => $customer['inv_zip'],
            "inv_region"                   => $customer['inv_region'],
            "inv_country"                  => $customer['inv_country'],

            "del_given_name"               => $address['del_given_name'],
            "del_family_name"              => $address['del_family_name'],
            "del_company_name"             => $address['del_company_name'],
            "del_street_1"                 => $address['del_street_1'],
            "del_street_2"                 => $address['del_street_2'],
            "del_floor"                    => $address['del_floor'],
            "del_city"                     => $address['del_city'],
            "del_zip"                      => $address['del_zip'],
            "del_region"                   => $address['del_region"'],
            "del_country"                  => $address['del_country'],

            "phone_number"                 => $address['phone_number'],
            "email"                        => $address['email'],

            "id_destination_country"       => $destinationCountriesIds[rand(0, count($destinationCountriesIds) - 1)],
            "id_delivery_service"          => $deliveryServicesIds[rand(0, count($deliveryServicesIds) - 1)],
            "id_payment_service"           => $paymentServicesIds[rand(0, count($paymentServicesIds) - 1)],

            "domain"                       => $domainsToInstall[rand(1, count($domainsToInstall))]["name"],
            "general_terms_and_conditions" => 1,
            "gdpr_consent"                 => 1,
            "confirmation_time"            => $orderConfirmationTime,
          ],
          $customerUID
        );

        if (rand(0, 1) == 1) {
          $idInvoice = $orderModel->issueInvoce($idOrder, TRUE);
          $orderTagAssignmentModel->insertRow(["id_order" => $idOrder, "id_tag" => rand(1,5)]);
        }

      }

      $adminPanel->console->info("Orders installed.");
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: website content

    $wsg = new WebsiteContentGenerator(
      $adminPanel,
      $slideshowImageSet,
      $domainsToInstall,
    );

    $wsg->copyAssets();

    foreach ($domainsToInstall as $domainIndex => $domain) {
      $wsg->generateWebsiteContent($domainIndex, $domain["themeName"]);
      $wsg->installPlugins();
      $adminPanel->widgets["Website"]->rebuildSitemap($domainsToInstall[$domainIndex]['name']);
    }

    $wsg->installPluginsOnce();

    $adminPanel->console->info("Website content installed.");


  } catch (\Exception $e) {
    _echo("
      <h2 style='color:red'>Error</h2>
      <div style='color:red'>
        ".get_class($e).": ".$e->getMessage()."
      </div>
    ");
    var_dump($e->getTrace());
    $adminPanel->console->error(get_class($e).": ".$e->getMessage());
  }

  $infos = $adminPanel->console->getInfos();
  $warnings = $adminPanel->console->getWarnings();
  $errors = $adminPanel->console->getErrors();

  if (count($errors) > 0) {
    _echo("
      <h2 style='color:red'>Awgh!</h2>
      <div style='color:red;margin-bottom:1em'>
        ✕ Some errors occured during the installation.
      </div>
      <div style='color:red'>".$adminPanel->console->convertLogsToHtml($errors)."</div>
    ");
  } else {
    _echo("
      <h2>Done in ".round((microtime(true) - $installationStart), 2)." seconds.</h2>
      <div style='color:green;margin-bottom:1em'>
        ✓ Congratulations. You have successfuly installed your eCommerce project.
      </div>
      <div style='color:orange;margin-bottom:1em'>
        ⚠ WARNING: You should delete the <i>install</i> folder now.
      </div>
      <table>
        <!-- <tr><td>Theme</td><td>{$themeName}</td></tr> -->
        <!-- <tr><td>Content language</td><td>{$languageToInstall}</td></tr> -->
        <tr><td>Slideshow image set</td><td>{$slideshowImageSet}</td></tr>
        <tr><td>Sample set of products</td><td>".(in_array("product-catalog", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Random products count</td><td>{$randomProductsCount}</td></tr>
        <tr><td>Sample set of customers</td><td>".(in_array("customers", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Sample set of delivery and payment services</td><td>".(in_array("delivery-and-payment-services", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Sample set of orders</td><td>".(in_array("orders", $partsToInstall) ? "yes" : "no")."</td></tr>
      </table>
      <br/>
      ".(count($warnings) > 0 ? "
        <h2>Warnings</h2>
        <div style='color:orange'>".$adminPanel->console->convertLogsToHtml($warnings)."</div>
      " : "")."
      <br/>
      <!-- <a href='..' class='btn' target=_blank>Go to your e-shop</a> -->
      <a href='../admin' class='btn' target=_blank>Open administration panel</a><br/>
      Login: administrator<br/>
      Password: administrator<br/>
    ");
  }

  _echo("
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
  ");

}

_echo("
    </div>
  </body>
  </html>
");