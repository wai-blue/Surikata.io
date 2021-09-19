<?php

namespace Surikata\Plugins\WAI\Product {
  class Filter extends \Surikata\Core\Web\Plugin {
    public static $filterInfo = NULL;

    public function getFilterInfo() {
      if (self::$filterInfo === NULL) {
        $idCategory = (int) $this->websiteRenderer->urlVariables["idProductCategory"] ?? 0;
        $brands = $this->websiteRenderer->urlVariables["brands"] ?? "";
        $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

        $brandModel = new \ADIOS\Widgets\Products\Models\Brand($this->adminPanel);
        $productFeatureModel = new \ADIOS\Widgets\Products\Models\ProductFeature($this->adminPanel);
        $productFeatureAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductFeatureAssignment($this->adminPanel);
        $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

        $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);

        $allBrands = $brandModel->getAllCached();
        $allFeatures = $productFeatureModel->getAllCached();
        $allFeaturesAssignments = $productFeatureAssignmentModel->getAllCached();
        $allCategories = $productCategoryModel->translateForWeb(
          $productCategoryModel->getAllCached(),
          $languageIndex
        );

        foreach ($allCategories as $key => $category) {
          $allCategories[$key]["url"] = $productCatalogPlugin->getWebPageUrl(
            $productCatalogPlugin->convertCategoryToUrlVariables($category)
          );
        }

        $allCategoriesAndSubCategories = $productCategoryModel->getAllCategoriesAndSubCategories($allCategories);

        if ($idCategory > 0) {
          $parentCategories = $productCategoryModel->extractParentCategories($idCategory, $allCategories);
          $allSubCategories = $productCategoryModel->extractAllSubCategories($idCategory, $allCategories);
          $directSubCategories = $productCategoryModel->extractDirectSubCategories($idCategory, $allCategories);
        }

        foreach ($allFeaturesAssignments as $value) {
          $allFeatures[$value['id_feature']]['minValue'] = min(
            $allFeatures[$value['id_feature']]['minValue'] ?? 0,
            (int) $value['value_number']
          );
          $allFeatures[$value['id_feature']]['maxValue'] = max(
            $allFeatures[$value['id_feature']]['maxValue'] ?? 0,
            (int) $value['value_number']
          );
        }

        self::$filterInfo = [
          "allBrands" => $allBrands,
          "allCategories" => $allCategories,
          "allCategoriesAndSubCategories" => $allCategoriesAndSubCategories,
          "parentCategories" => $parentCategories,
          "allSubCategories" => $allSubCategories,
          "directSubCategories" => $directSubCategories,
          "allFeatures" => $allFeatures,
          "idCategory" => $idCategory,
        ];

        if (!empty($brands)) {
          if (is_string($brands)) {
            self::$filterInfo['filteredBrands'] = explode(" ", $brands);
          } else if (is_array($brands)) {
            self::$filterInfo['filteredBrands'] = $brands;
          }
        }

      }

      return self::$filterInfo;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["filterInfo"] = $this->getFilterInfo();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Filter extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "layout" => [
          "title" => "Spôsob zobrazenia",
          "type" => "varchar",
          "enum_values" => [
            "" => "Zvoľte spôsob zobrazenia filtra",
            "sidebar" => "V bočnom paneli",
          ],
        ],
        "showProductCategories" => [
          "title" => "Zobraziť kategórie",
          "type" => "boolean",
        ],
        "zobrazit_filter" => [
          "title" => "Zobraziť filter",
          "type" => "boolean",
        ],
        "show_brands" => [
          "title" => "Zobraziť výrobcov",
          "type" => "boolean",
        ],
      ];
    }
    
  }
}