<?php

namespace Surikata\Plugins\WAI\Product {
  class Filter extends \Surikata\Core\Web\Plugin {
    public static $filterInfo = NULL;
    public static $allCategories = [];

    public function getFilterInfo() {
      if (self::$filterInfo === NULL) {
        $idCategory = (int) $this->websiteRenderer->urlVariables["idCategory"] ?? 0;
        $idBrand = (int) $this->websiteRenderer->urlVariables["idBrand"] ?? 0;
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

        if (empty(self::$allCategories)) {
          self::$allCategories = $productCategoryModel->pdoPrepareExecuteAndFetch(
            "select * from :table order by order_index",
            [],
            "id"
          );
          
          self::$allCategories = $productCategoryModel->translateForWeb(
            self::$allCategories,
            $languageIndex
          );
        }

        foreach (self::$allCategories as $key => $category) {
          self::$allCategories[$key]["url"] = $productCatalogPlugin->getWebPageUrl(
            $productCatalogPlugin->extractUrlVariablesFromCategory($category)
          );
        }

        $allCategoriesAndSubCategories = $productCategoryModel->getAllCategoriesAndSubCategories(self::$allCategories);

        if ($idCategory > 0) {
          $parentCategories = $productCategoryModel->extractParentCategories($idCategory, self::$allCategories);
          $allSubCategories = $productCategoryModel->extractAllSubCategories($idCategory, self::$allCategories);
          $directSubCategories = $productCategoryModel->extractDirectSubCategories($idCategory, self::$allCategories);
          // Nacitam momentalnu kategoriu a jej sub kategorie
          $currentAndAllSubCategories = $productCategoryModel->extractCurrentAndAllSubCategories($idCategory, self::$allCategories);
        }

        $filteredBrands = [];

        if (!empty($brands)) {
          if (is_string($brands)) {
            $filteredBrands = explode(" ", $brands);
          } else if (is_array($brands)) {
            $filteredBrands = $brands;
          }
        }

        if ($idBrand > 0) {
          $filteredBrands[] = $idBrand;
          $filteredBrands = array_unique($filteredBrands);
        }

        foreach ($filteredBrands as $key => $value) {
          $value = (int) $value;
          if ($value <= 0) {
            unset($filteredBrands[$key]);
          }
        }

        self::$filterInfo = $this->adminPanel->dispatchEventToPlugins("onProductCatalogGetFilterInfo", [
          "filter" => [
            "allBrands" => $allBrands,
            "allCategories" => self::$allCategories,
            "allCategoriesAndSubCategories" => $allCategoriesAndSubCategories,
            "parentCategories" => $parentCategories,
            "allSubCategories" => $allSubCategories,
            "directSubCategories" => $directSubCategories,
            "allFeatures" => $allFeatures,
            "currentAndAllSubCategories" => $currentAndAllSubCategories,
            "idCategory" => $idCategory,
            "filteredBrands" => $filteredBrands,
            "allFeaturesAssignments" => $allFeaturesAssignments
          ],
        ])["filter"];

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

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => "Products - Sidebar filter",
      ];
    }
    public function getSettingsForWebsite() {
      return [
        "layout" => [
          "title" => "Layout",
          "type" => "varchar",
          "enum_values" => [
            "" => "Choose layout",
            "sidebar" => "Sidebar layout",
          ],
        ],
        "showProductCategories" => [
          "title" => "Show product categories",
          "type" => "boolean",
        ],
        "showFeaturesFilter" => [
          "title" => "Show features filter",
          "type" => "boolean",
        ],
        "showBrands" => [
          "title" => "Show brands",
          "type" => "boolean",
        ],
      ];
    }
    
  }
}