<?php

namespace Surikata\Plugins\WAI\Product {
  class FilteredList extends \Surikata\Core\Web\Plugin {

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adminPanel);
      $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

      switch ($pluginSettings["filterType"]) {
        case "recommended":
          $productIds = $productModel
            ->where("is_recommended", TRUE)
            ->skip(0)->take((int)$pluginSettings["product_count"]);
        break;
        case "on_sale":
          $productIds = $productModel->where("is_on_sale", TRUE);
        break;
        case "sale_out":
          $productIds = $productModel->where("is_sale_out", TRUE);
        break;
      }

      $productIds = $productIds
        ->skip(0)
        ->take((int) $pluginSettings["product_count"])
        ->get()
        ->pluck('id')
      ;

      $twigParams["products"] = $productModel->getDetailedInfoForListOfProducts($productIds);

      $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
      foreach ($twigParams["products"] as $key => $product) {
        $twigParams["products"][$key]["url"] =
          $productDetailPlugin->getWebPageUrl($product)
        ;

        $twigParams["products"][$key] =
          $productModel->translateSingleProductForWeb($twigParams["products"][$key], $languageIndex);

        $twigParams["products"][$key]["ProductCategory"] =
          $productCategoryModel
            ->translateForWeb(
              [
                $productCategoryModel
                ->getById($twigParams["products"][$key]["id_category"])
              ], $languageIndex
            )[0];
      }

      return $twigParams;
    }
  }

}

namespace ADIOS\Plugins\WAI\Product {
  class FilteredList extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "filterType" => [
          "title" => "Products for display",
          "type" => "varchar",
          "enum_values" => [
            "on_sale" => "Discounted products",
            "news" => "New products",
            "recommended" => "Recommended products",
            "top" => "Top products",
          ]
        ],
        "layout" => [
          "title" => "Display mode",
          "type" => "varchar",
          "enum_values" => [
            "tiles" => "Tiles",
            "row" => "Row",
            "row-large" => "Row large"
          ],
        ],
        "product_count" => [
          "title" => "Number of products",
          "type" => "int",
        ],
      ];
    }

  }
}