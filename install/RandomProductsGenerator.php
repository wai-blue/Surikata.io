<?php


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
    ADIOS\Widgets\Products\Models\Product $productModel,
    ADIOS\Widgets\Products\Models\ProductFeatureAssignment $productFeatureAssignmentModel
  ) {
    $productsData = [];
    for ($i = 0; $i < $numOfProducts; $i++) {
      $productImgNum = rand(1, 7);
      $idCategory = rand(1, 7);
      $idBrand = rand(1, 7);
      $name = "RND Product ".$i;
      $brief = "Brief for RND Product ".$i;
      $description = "Description for RND Product ".$i;
      $price = rand(500, 5000)/100;
      $features = [rand(1000,1200), rand(1200,1250), rand(250,300), rand(1,3), rand(75,120)*10, rand(50,100)*10, "155 R13"];
      $number = "RND.".rand(10, 99).".".rand(1000, 9999).".".$i;
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
        /* 7 */ $features,
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

      $tmpFeatureId = 1;
      foreach ($tmpProduct[7] as $tmpFeatureValue) {
        $productFeatureAssignmentModel->insertRow([
          "id_product" => $idProduct,
          "id_feature" => $tmpFeatureId,
          "value_text" => $tmpFeatureValue,
          "value_number" => $tmpFeatureValue,
        ]);
        $tmpFeatureId++;
      }

      $i++;
    }
  }
}