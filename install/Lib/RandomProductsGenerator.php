<?php


namespace Surikata\Installer;

class RandomProductsGenerator {

  public static function generateEAN($number) {
    $code = '200' . str_pad($number, 9, '0');
    $weightflag = true;
    $sum = 0;
    // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit. 
    // loop backwards to make the loop length-agnostic. The same basic functionality 
    // will work for codes of different lengths.
    for ($i = strlen($code) - 1; $i >= 0; $i--)
    {
      $sum += (int)$code[$i] * ($weightflag?3:1);
      $weightflag = !$weightflag;
    }
    $code .= (10 - ($sum % 10)) % 10;
    return $code;
  }

  public static function generateRandomProducts(
    int $numOfProducts,
    \ADIOS\Widgets\Products\Models\Product $productModel,
    \ADIOS\Widgets\Products\Models\ProductFeatureAssignment $productFeatureAssignmentModel,
    \ADIOS\Widgets\Products\Models\ProductCategory $productCategoryModel,
    \ADIOS\Widgets\Products\Models\ProductFeature $productFeatureModel,
  ) {
    $allCategories = $productCategoryModel->getAllCached();
    $allFeatures = $productFeatureModel->getAllCached();

    $parentCategories = [];
    foreach ($allCategories as $category) {
      $parentCategory = $productCategoryModel->extractParentCategories($category["id"], $allCategories)[1];
      $parentCategories[$parentCategory["id"]] = $parentCategory;
    }

    $randomProductNames = [
      "Edifier H840 Audiophile",
      "SoundBox Pro Portable",
      "Wireless Stereo Speaker",
      "Naham WiFi HD 1080P",
      "Sony WH-CH700N",
      "Polk Audio T30 Speaker",
      "Silicon Sleeping Earbuds",
      "Numkuda USB 2.0 Gamepad",
      "TCL 49S5 49” 4K Ultra HD",
      "JBL Flip 3 Splasroof Portable Bluetooth 2",
      "Koss Porta Pro On Ear Headphones",
      "Bose SoundLink Bluetooth Speaker",
      "Accusantium dolorem Security Camera",
    ];

    $productsData = [];
    $colors = ["White", "Green", "Blue", "Yellow", "Red"];
    $ram = ["2GB", "4GB", "6GB", "8GB"];
    $showOnly = ["Classic","Premium", "Exclusive"];
    $condition = ["New", "Used", "Unpacked"];
    $type = ["Gaming", "Office"];
    $vacuumcleanersType = ["Robotically", "Manual"];
    $bearingType = ["SSD", "HDD", "SSD + HDD"];
    $equipment = ["With numeric keyboard", "Illuminated keyboard", "Touchscreen", "TrackPoint"];
    $tvResolutions = ["8K", "4K", "FULL HD"];
    $modelYear = [2018, 2019, 2020, 2021, 2022];
    $monitorFrequency = ["60HZ", "120HZ", "144HZ", "240HZ"];
    $keyboardType = ["Mechanical", "Membrane"];
    $energyClass = ["A", "B", "C", "D", "E"];
    $playstationsType = ["Playstation 4", "Playstation 5"];
    $pcGamesType = ["Shooting games", "For girls"];
    $sizes = ["XS", "S", "M", "L", "XL"];

    for ($i = 0; $i < $numOfProducts; $i++) {
      $idCategory = rand(7, 21);
      $productImgNum = rand(1, 7);
      $idBrand = rand(1, 7);
      $name = $allCategories[$idCategory]["name_lang_1"]." (rnd-{$i})";
      $brief = "Brief for RND Product ".$i.". Nam malesuada, dui eu aliquam elementum, lacus risus congue orci, eu varius dui enim.";
      $description = "
        Description for RND Product ".$i.". Duis ac pharetra tellus, ac pellentesque risus. Integer varius dictum sapien in venenatis. Sed varius
        sapien tincidunt faucibus ultrices. Curabitur bibendum lorem vel urna vulputate dignissim. Nam sed orci lobortis,
        placerat neque eu, tempus purus. Sed sit amet erat vitae nisi hendrerit tincidunt et nec odio. Nam ac ex nec libero
        venenatis consectetur ut a felis. Nam malesuada, dui eu aliquam elementum, lacus risus congue orci, eu varius dui enim
        at nibh. Praesent commodo felis luctus iaculis sodales. Ut ac blandit quam, a convallis risus.
      ";
      $price = rand(500, 5000)/100;
      $number = "RND.".$i;
      $ean = self::generateEAN($number);
      $image = "products/{$productImgNum}.jpg";
      $vat = 20;
      $productsData[$i] = [
        /* 0 */ $idCategory,
        /* 1 */ $idBrand,
        /* 2 */ "10".$i,
        /* 3 */ $name,
        /* 4 */ $brief,
        /* 5 */ $description,
        /* 6 */ $price,
        /* 7 */ "",
        /* 8 */ rand(0, 1),
        /* 9 */ rand(0, 1),
        /* 10 */ rand(0, 1),
        /* 11 */ $number,
        /* 12 */ $ean,
        /* 13 */ $image,
        /* 14 */ $vat
      ];
    }

    $i = 0;
    foreach ($productsData as $tmpProduct) {
      $tmpSalePrice = $tmpProduct[6];

      if (rand(0, 5) == 1) {
        $tmpFullPrice = $tmpProduct[6]*(1 + rand(0, 50)/100);
      } else {
        $tmpFullPrice = $tmpSalePrice;
      }

      $idProduct = $productModel->insertRow([
        "id_category" => $tmpProduct[0],
        "id_brand" => $tmpProduct[1],
        "price_calculation_method" => \ADIOS\Widgets\Products\Models\Product::PRICE_CALCULATION_METHOD_CUSTOM_PRICE,
        "full_price_excl_vat_custom" => $tmpFullPrice,
        "sale_price_excl_vat_custom" => $tmpSalePrice,
        "full_price_incl_vat_custom" => $tmpFullPrice * (1 + $tmpProduct[14] / 100),
        "sale_price_incl_vat_custom" => $tmpSalePrice * (1 + $tmpProduct[14] / 100),
        "full_price_excl_vat_cached" => $tmpFullPrice,
        "sale_price_excl_vat_cached" => $tmpSalePrice,
        "full_price_incl_vat_cached" => $tmpFullPrice * (1 + $tmpProduct[14] / 100),
        "sale_price_incl_vat_cached" => $tmpSalePrice * (1 + $tmpProduct[14] / 100),
        "id_delivery_unit" => rand(2, 21),
        "is_new" => $tmpProduct[9],
        "is_top" => $tmpProduct[10],
        "is_recommended" => rand(0, 1) == 1,
        "is_on_sale" => rand(0, 1) == 1,
        "is_sale_out" => rand(0, 1) == 1,
        "extended_warranty" => rand(0, 36),
        "stock_quantity" => rand(0, 100) / 10,
        "number" => $tmpProduct[11],
        "ean" => $tmpProduct[12],
        "image" => $tmpProduct[13],
        "vat_percent" => $tmpProduct[14],
        "weight" => rand(500, 2500),

        "name_lang_1"        => $tmpProduct[3]." (lng-1)",
        "brief_lang_1"       => $tmpProduct[4]." (lng-1)",
        "description_lang_1" => $tmpProduct[5]." (lng-1)",
        "name_lang_2"        => $tmpProduct[3]." (lng-2)",
        "brief_lang_2"       => $tmpProduct[4]." (lng-2)",
        "description_lang_2" => $tmpProduct[5]." (lng-2)",
        "name_lang_3"        => $tmpProduct[3]." (lng-3)",
        "brief_lang_3"       => $tmpProduct[4]." (lng-3)",
        "description_lang_3" => $tmpProduct[5]." (lng-3)",
      ]);

      $features = [];

      switch ($tmpProduct[0]) { // idCategory
        case 7: // Desktop computers
        case 8: // Notebooks
          $features = [
            1 => rand(1,1000), 
            2 => rand(1,1000), 
            3 => rand(1,1000), 
            4 => $ean, 
            6 => $ram[rand(0, 3)], 
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            10 => $bearingType[rand(1, 2)],
            11 => $equipment[rand(1,3)],
          ];
        break;
        case 9: // Monitors
          $features = [
            1 => rand(1,1000), 
            2 => rand(1,1000), 
            3 => rand(1,1000), 
            4 => $ean, 
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            14 => $monitorFrequency[rand(0, 3)]
          ];
        break;
        case 10: // Keyboards
          $features = [
            1 => rand(1,1000), 
            2 => rand(1,1000), 
            3 => rand(1,1000), 
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            15 => $keyboardType[rand(0, 1)]
          ];
        break;
        case 11: // Televisions
          $features = [
            1 => rand(1,1000), 
            2 => rand(1,1000), 
            3 => rand(1,1000), 
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            12 => $tvResolutions[rand(1, 2)],
            13 => $modelYear[rand(0, 3)]
          ];
        break;
        case 12: // Foto
          $features = [
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            13 => $modelYear[rand(0, 3)]
          ];
        break;
        case 13: // Washers and dryers
        case 14: // Freezers
          $features = [
            1 => rand(1,1000), 
            2 => rand(1,1000), 
            3 => rand(1,1000), 
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $type[rand(0, 1)],
            13 => $modelYear[rand(0, 3)],
            16 => $energyClass[rand(0, 4)]
          ];
        break;
        case 15: // Vacuumcleaners
          $features = [
            3 => rand(1,1000), 
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            9 => $vacuumcleanersType[rand(0, 1)],
            13 => $modelYear[rand(0, 3)],
            16 => $energyClass[rand(0, 4)]
          ];
        break;
        case 16: // Stuffed animals
          $features = [
            4 => $ean, 
            5 => $colors[rand(0, 4)],
          ];
        break;
        case 17: // Playstations
          $features = [
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            7 => $showOnly[rand(0, 2)],
            8 => $condition[rand(0, 2)],
            17 => $playstationsType[rand(0 ,2)]
          ];
        break;
        case 19: // PC games
          $features = [
            4 => $ean, 
            9 => $pcGamesType[rand(0, 1)]
          ];
        break;
        case 20: // PC games
          $features = [
            4 => $ean, 
            5 => $colors[rand(0, 4)]
          ];
        break;
        case 21: // T-shirts
          $features = [
            4 => $ean, 
            5 => $colors[rand(0, 4)],
            18 => $sizes[rand(0, 4)]
          ];
        break;
      }

      if (!empty($features)) {
        foreach ($features as $featureId => $featureValue) {
          $productFeatureAssignmentModel->insertRow([
            "id_product" => $idProduct,
            "id_feature" => $featureId,
            "value_text" => $featureValue,
            "value_number" => $featureValue,
            "value_boolean" => $featureValue,
          ]);
        }
      }

      $i++;
    }
  }
}