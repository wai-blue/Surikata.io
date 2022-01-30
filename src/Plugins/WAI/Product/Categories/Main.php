<?php

namespace Surikata\Plugins\WAI\Product {
  class Categories extends \Surikata\Core\Web\Plugin {
    public static $filterInfo = NULL;

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

      $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);
      $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);
      $allCategories = $productCategoryModel->translateForWeb(
        $productCategoryModel->getAllCached(),
        $languageIndex
      );

      foreach ($allCategories as $key => $category) {
        $allCategories[$key]["url"] = $productCatalogPlugin->getWebPageUrl(
          $productCatalogPlugin->extractUrlVariablesFromCategory($category)
        );
      }

      $twigParams = [
        "categories" => $productCategoryModel->getAllCategoriesAndSubCategories($allCategories),
      ];


      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Categories extends \Surikata\Core\AdminPanel\Plugin {
  }
}