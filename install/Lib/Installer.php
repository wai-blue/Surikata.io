<?php

namespace Surikata\Installer;

use \Surikata\Installer\HelperFunctions;

class Installer {

  public static function installSurikataProject(
    $adminPanelConfig,
    $websiteRendererConfig,
    $installationConfig
  ) {

    $createPackage = $installationConfig['create_package'] ?? "";

    if (!empty($createPackage) && !class_exists("\\ZipArchive")) {
      throw new \Exception("Cannot create package. ZipArchive class not found.");
    }

    $domainsToInstall = HelperFunctions::parseDomainsToInstall($installationConfig);

    $randomProductsCount = $installationConfig['random-products-count'] ?? 50;
    if ($randomProductsCount > 100000) $randomProductsCount = 100000;

    $partsToInstall = [];
    if (($installationConfig['product-catalog'] ?? "") == "yes") $partsToInstall[] = "product-catalog";
    if (($installationConfig['delivery-and-payment-services'] ?? "") == "yes") $partsToInstall[] = "delivery-and-payment-services";
    if (($installationConfig['customers'] ?? "") == "yes") $partsToInstall[] = "customers";
    if (($installationConfig['orders'] ?? "") == "yes") $partsToInstall[] = "orders";

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialization

    $websiteRenderer = new \MyEcommerceProject\Web($websiteRendererConfig);
    $adminPanel = new \MyEcommerceProject\AdminPanel(
      $adminPanelConfig,
      \ADIOS\Core\Loader::ADIOS_MODE_FULL,
      $websiteRenderer
    );
    $adminPanel->console->cliEchoEnabled = TRUE;

    $adminPanel->console->info("Installation started.");

    HelperFunctions::recursiveRmDir($adminPanel->config["files_dir"], [".htaccess"]);
    HelperFunctions::recursiveRmDir($adminPanelConfig["cache_dir"], [".htaccess"]);

    $adminPanel->createMissingFolders();

    // delete the contents of upload/ folder

    // query buffer is created if we want to create a package
    if (!empty($createPackage)) {
      $adminPanel->db->startQueryBuffering();
    }

    $adminPanel->install();
    $adminPanel->installDefaultUsers();

    $adminPanel->db->query("SET foreign_key_checks = 0");

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

    $deliveryServiceModel = new \ADIOS\Widgets\Shipping\Models\DeliveryService($adminPanel);
    $destinationCountryModel = new \ADIOS\Widgets\Shipping\Models\DestinationCountry($adminPanel);
    $paymentServiceModel = new \ADIOS\Widgets\Shipping\Models\PaymentService($adminPanel);
    $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($adminPanel);
    $shipmentPriceModel = new \ADIOS\Widgets\Shipping\Models\ShipmentPrice($adminPanel);

    // ConfigEnvDomains.php
    file_put_contents(
      PROJECT_ROOT_DIR."/ConfigEnvDomains.php",
      HelperFunctions::renderConfigEnvDomains($domainsToInstall)
    );

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
      $unitModel->insertRow(["id" => 1, "unit" => "-", "name_lang_1" => "no unit", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 2, "unit" => "mm", "name_lang_1" => "milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 3, "unit" => "cm", "name_lang_1" => "centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 4, "unit" => "m", "name_lang_1" => "meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 22, "unit" => "km", "name_lang_1" => "kilometers", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 5, "unit" => "ml", "name_lang_1" => "mililitres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 6, "unit" => "dl", "name_lang_1" => "decilitres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 7, "unit" => "l", "name_lang_1" => "litres", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 8, "unit" => "mg", "name_lang_1" => "miligramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 9, "unit" => "g", "name_lang_1" => "gramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 10, "unit" => "kg", "name_lang_1" => "kilogramms", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 11, "unit" => "t", "name_lang_1" => "tonnes", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 12, "unit" => "mm2", "name_lang_1" => "square milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 13, "unit" => "cm2", "name_lang_1" => "square centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 14, "unit" => "m2", "name_lang_1" => "square meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 15, "unit" => "mm3", "name_lang_1" => "cubic milimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 16, "unit" => "cm3", "name_lang_1" => "cubic centimeters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 17, "unit" => "m3", "name_lang_1" => "cubic meters", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 18, "unit" => "btl", "name_lang_1" => "bottles", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 19, "unit" => "pcs", "name_lang_1" => "pieces", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 20, "unit" => "pkg", "name_lang_1" => "packages", "is_for_products" => TRUE, "is_for_features" => TRUE]);
      $unitModel->insertRow(["id" => 21, "unit" => "cnt", "name_lang_1" => "containers", "is_for_products" => TRUE, "is_for_features" => TRUE]);

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
        "id_parent" => 0,
        "code" => "Computers",
        "name_lang_1" => "Computers and notebooks",
        "name_lang_2" => "Počítače a notebooky",
        "name_lang_3" => "Počítače a notebooky"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 0,
        "code" => "TV",
        "name_lang_1" => "TV, foto",
        "name_lang_2" => "TV, foto",
        "name_lang_3" => "TV, foto"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 0,
        "code" => "Appliances",
        "name_lang_1" => "Household Appliances",
        "name_lang_2" => "Domáce spotrebiče",
        "name_lang_3" => "Domáce spotrebiče"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 0,
        "code" => "Toys",
        "name_lang_1" => "Toys for kids",
        "name_lang_2" => "Hračky pre deti",
        "name_lang_3" => "Hračky pre deti"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 0,
        "code" => "Games",
        "name_lang_1" => "Games and entertainment",
        "name_lang_2" => "Hry a zábava",
        "name_lang_3" => "Hry a zábava"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 0,
        "code" => "Clothes",
        "name_lang_1" => "Clothes and footwear",
        "name_lang_2" => "Oblečenie a obuv",
        "name_lang_3" => "Oblečenie a obuv"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 1,
        "code" => "DesktopComputers",
        "name_lang_1" => "Desktop computers",
        "name_lang_2" => "Stolné počítače",
        "name_lang_3" => "Stolné počítače"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 1,
        "code" => "Notebooks",
        "name_lang_1" => "Notebooks",
        "name_lang_2" => "Notebooky",
        "name_lang_3" => "Notebooky"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 1,
        "code" => "Monitors",
        "name_lang_1" => "Monitors",
        "name_lang_2" => "Monitory",
        "name_lang_3" => "Monitory"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 1,
        "code" => "Keyboards",
        "name_lang_1" => "Keyboards",
        "name_lang_2" => "Klávesnice",
        "name_lang_3" => "Klávesnice"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 2,
        "code" => "Televisions",
        "name_lang_1" => "Televisions",
        "name_lang_2" => "Televízory",
        "name_lang_3" => "Televízory"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 2,
        "code" => "PhotoCameras",
        "name_lang_1" => "Photo cameras",
        "name_lang_2" => "Fotoaparáty",
        "name_lang_3" => "Fotoaparáty"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 3,
        "code" => "Buckles",
        "name_lang_1" => "Washers and dryers",
        "name_lang_2" => "Pračky a sušičky",
        "name_lang_3" => "Pračky a sušičky"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 3,
        "code" => "Freezers",
        "name_lang_1" => "Freezers",
        "name_lang_2" => "Mrazničky",
        "name_lang_3" => "Mrazničky"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 3,
        "code" => "VacuumCleaners",
        "name_lang_1" => "Vacuum cleaners and housework",
        "name_lang_2" => "Vysávače a domáce práce",
        "name_lang_3" => "Vysávače a domáce práce"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 4,
        "code" => "StuffedAnimals",
        "name_lang_1" => "Stuffed animals",
        "name_lang_2" => "Plyšové hračky",
        "name_lang_3" => "Plyšové hračky"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 5,
        "code" => "Playstation",
        "name_lang_1" => "Playstation",
        "name_lang_2" => "Playstation",
        "name_lang_3" => "Playstation"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 5,
        "code" => "Xboxs",
        "name_lang_1" => "Xboxs",
        "name_lang_2" => "Xboxs",
        "name_lang_3" => "Xboxs"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 5,
        "code" => "PCgames",
        "name_lang_1" => "PC games",
        "name_lang_2" => "Počitačové hry",
        "name_lang_3" => "Počitačové hry"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 6,
        "code" => "Shoes",
        "name_lang_1" => "Shoes",
        "name_lang_2" => "Obuv",
        "name_lang_3" => "Obuv"
      ]);
      $productCategoryModel->insertRow([
        "id_parent" => 6,
        "code" => "T-shirts",
        "name_lang_1" => "T-shirts",
        "name_lang_2" => "Tričká",
        "name_lang_3" => "Tričká"
      ]);
      
      // produkty - vlastnosti produktov, ciselnik
      $productFeatureModel->insertRow(["id" => 1, "order_index" => 1, "value_type" => 1, "entry_method" => 1, "min" => 1, "max" => 1000, "name_lang_1" => "Length", "name_lang_2" => "Dĺžka", "id_measurement_unit" => 2]);
      $productFeatureModel->insertRow(["id" => 2, "order_index" => 2, "value_type" => 1, "entry_method" => 1, "min" => 1, "max" => 1000, "name_lang_1" => "Width", "name_lang_2" => "Šírka", "id_measurement_unit" => 2]);
      $productFeatureModel->insertRow(["id" => 3, "order_index" => 3, "value_type" => 1, "entry_method" => 1, "min" => 1, "max" => 1000, "name_lang_1" => "Weight", "name_lang_2" => "Váha", "id_measurement_unit" => 9]);
      $productFeatureModel->insertRow(["id" => 4, "order_index" => 4, "value_type" => 2, "entry_method" => 5, "min" => null, "min" => null, "name_lang_1" => "EAN", "name_lang_2" => "EAN", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 5, "order_index" => 5, "value_type" => 2, "entry_method" => 4, "min" => null, "max" => null, "name_lang_1" => "Color", "name_lang_2" => "Farba", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 6, "order_index" => 6, "value_type" => 2, "entry_method" => 4, "min" => null, "max" => null, "name_lang_1" => "RAM", "name_lang_2" => "Pamäť RAM", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 7, "order_index" => 7, "value_type" => 2, "entry_method" => 2, "min" => null, "min" => null, "name_lang_1" => "Show only", "name_lang_2" => "Zobraziť iba", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 8, "order_index" => 8, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Condition", "name_lang_2" => "Stav", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 9, "order_index" => 9, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Type", "name_lang_2" => "Typ", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 10, "order_index" => 10, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Bearing type", "name_lang_2" => "Typ úložiska", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 11, "order_index" => 11, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Equipment", "name_lang_2" => "Výbava", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 12, "order_index" => 12, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Max resolutions", "name_lang_2" => "Maximálne rozlíšenie", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 13, "order_index" => 13, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Model year", "name_lang_2" => "Modelový rok", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 14, "order_index" => 14, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Monitor frequency", "name_lang_2" => "Frekvecia monitoru", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 15, "order_index" => 15, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Keyboard type", "name_lang_2" => "Typ klávesnice", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 16, "order_index" => 16, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Energy class", "name_lang_2" => " Energetická trieda", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 17, "order_index" => 17, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Playstation version", "name_lang_2" => "Verzia Playstation", "id_measurement_unit" => 1]);
      $productFeatureModel->insertRow(["id" => 18, "order_index" => 18, "value_type" => 2, "entry_method" => 3, "min" => null, "min" => null, "name_lang_1" => "Size", "name_lang_2" => "Veľkosť trička", "id_measurement_unit" => 1]);

      $productFeaturesCount = $productFeatureModel->get()->count();

      // produkty - stavy na sklade
      $productStockStateModel->insertRow(["id" => 1, "name_lang_1" => "Available in stock", "name_lang_2" => "Dostupné na sklade", "name_lang_3" => "Dostupné na skladě"]);
      $productStockStateModel->insertRow(["id" => 2, "name_lang_1" => "Currently unavailable", "name_lang_2" => "Nedostupné", "name_lang_3" => "Nedostupné"]);
      $productStockStateModel->insertRow(["id" => 3, "name_lang_1" => "Available upon request", "name_lang_2" => "Dostupné na dotaz", "name_lang_3" => "Dostupné na dotaz"]);

      // produkty - produkty
      $adminPanel->db->startTransaction();

      \Surikata\Installer\RandomProductsGenerator::generateRandomProducts(
        $randomProductsCount,
        $productModel,
        $productFeatureAssignmentModel,
        $productCategoryModel,
        $productFeatureModel
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
            "image" => "products/".rand(1, 10).".jpg",
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
      $customers = HelperFunctions::loadCsvIntoArray(__DIR__."/../content/Customers.csv");

      $cnt = 1;
      for ($i = 0; $i < 10; $i++) {
        foreach ($customers as $customer) {
          $tmpPassword = password_hash("0000", PASSWORD_DEFAULT);

          $idCustomer = $customerModel->insertRow([
            "id_category" => $customer[0],
            "email" => strtolower("{$customer[1]}.{$customer[2]}.{$cnt}@example.com"),
            "phone_number" => "+1 123 456 789",

            "inv_given_name" => $customer[1],
            "inv_family_name" => $cnt." ".$customer[2],
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
            // "del_email" => strtolower("{$customer[1]}.{$customer[2]}@example.com"),
            "del_email" => "example@email.com",
            "del_phone_number" => "+1 123 456 789",
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
            "phone_number"      => $customer['phone_number'],
            "email"             => $customer['email'],

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
            "del_phone_number"             => $address['del_phone_number'],
            "del_email"                    => $address['del_email'],

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
          $orderTagAssignmentModel->insertRow(["id_order" => $idOrder, "id_tag" => rand(1,3)]);
        }

      }

      $adminPanel->console->info("Orders installed.");
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PART: website content

    $wsg = new \Surikata\Installer\WebsiteContentGenerator(
      $adminPanel,
      $domainsToInstall,
      $installationConfig
    );

    $wsg->copyAssets();

    foreach ($domainsToInstall as $domainIndex => $domain) {
      $wsg->generateWebsiteContent($domainIndex, $domain["themeName"]);
      $wsg->installPlugins();
      $adminPanel->widgets["Website"]->rebuildSitemap($domainsToInstall[$domainIndex]['name']);
    }

    $wsg->installPluginsOnce();

    $adminPanel->console->info("Website content installed.");

    $infos = $adminPanel->console->getInfos();
    $warnings = $adminPanel->console->getWarnings();
    $errors = $adminPanel->console->getErrors();

    if (!empty($createPackage) && strpos($createPackage, ".") === FALSE) {
      $buffer = $adminPanel->db->stopQueryBuffering();

      $packageFilename = $createPackage.".zip";

      $zip = new \ZipArchive;
      $res = $zip->open(PROJECT_ROOT_DIR."/install/packages/{$packageFilename}", \ZipArchive::CREATE);

      if ($res === TRUE) {
        $zip->addFromString("package.sql", $buffer);
        $zip->addFromString("installation-config.json", json_encode($installationConfig));
        $zip->addFromString("info.log", $adminPanel->console->convertLogsToPlainText($infos, TRUE));
        $zip->addFromString("warning.log", $adminPanel->console->convertLogsToPlainText($warnings, TRUE));
        $zip->addFromString("error.log", $adminPanel->console->convertLogsToPlainText($errors, TRUE));

        HelperFunctions::addFolderToZip($zip, $adminPanel->config["files_dir"], "upload");

        $zip->close();

      } else {
        $adminPanel->console->error("Failed to create package.");
      }
    }

    $infosHtml = $adminPanel->console->convertLogsToHtml($infos);
    $warningsHtml = $adminPanel->console->convertLogsToHtml($warnings);
    $errorsHtml = $adminPanel->console->convertLogsToHtml($errors);

    return [
      "infos" => $infos,
      "warnings" => $warnings,
      "errors" => $errors,
      "infosHtml" => $infosHtml,
      "warningsHtml" => $warningsHtml,
      "errorsHtml" => $errorsHtml,
    ];

  }
}

