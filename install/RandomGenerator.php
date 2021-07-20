<?php


class RandomGenerator {

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
      $idCategory = rand(1, 7);
      $idBrand = rand(1, 7);
      $name = "RND Product ".$i;
      $brief = "Brief for RND Product ".$i;
      $description = "Description for RND Product ".$i;
      $price = rand(5, 5000)*10;
      $features = [rand(1000,1200), rand(1200,1250), rand(250,300), rand(1,3), rand(75,120)*10, rand(50,100)*10, "155 R13"];
      $number = "RND.".rand(10, 99).".".rand(1000, 9999);
      $ean = self::generateEAN($number);

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
        /* 12 */ $ean
      ];
    }

    $i = 0;
    foreach ($productsData as $tmpProduct) {
      $productImgNum = rand(1, 7);
      $idProduct = $productModel->insertRow([
        "id_category" => $tmpProduct[0],
        "id_brand" => $tmpProduct[1],
        "number" => $tmpProduct[2],
        "name_lang_1" => $tmpProduct[3],
        "brief_lang_1" => $tmpProduct[4],
        "description_lang_1" => $tmpProduct[5],
        "sale_price" => $tmpProduct[6],
        "id_delivery_unit" => rand(2, 21),
        "is_on_sale" => $tmpProduct[8],
        "is_new" => $tmpProduct[9],
        "is_top" => $tmpProduct[10],
        "is_recommended" => rand(0,1) == 1,
        "image" => "products/product_{$productImgNum}.jpg",
        "extended_warranty" => rand(0, 36),
        "stock_quantity" => rand(0, 100) / 10,
        "number" => $tmpProduct[11],
        "ean" => $tmpProduct[12],
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