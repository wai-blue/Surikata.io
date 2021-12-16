<?php

namespace Surikata\Plugins\WAI\Product {
  class Catalog extends \Surikata\Core\Web\Plugin {
    public static $catalogInfo = NULL;
    var $currentCategory = NULL;

    // must be the same as in ADIOS\Plugins namespace
    var $defaultUrlForCategories = "{% urlizedCategoryName %}.cid.{% idCategory %}";
    var $defaultUrlForBrands = "products/brand/{% idBrand %}/{% urlizedBrandName %}";

    public function getBreadcrumbs($urlVariables = []) {
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $breadcrumbs = [];

      $categoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

      if ($this->currentCategory === NULL) {
        $this->currentCategory = 
          $urlVariables['idCategory'] 
            ? $categoryModel->getById($urlVariables['idCategory'])
            : NUll
        ;
      }

      if ($this->currentCategory) {
        $allCategories = $categoryModel->getAllCached();
        $allCategories = $categoryModel->translateForWeb($allCategories, $languageIndex);

        $parentCategories = array_reverse(
          $categoryModel->extractParentCategories(
            $this->currentCategory['id'], 
            $allCategories
          )
        );

        foreach ($parentCategories as $category) {
          $url = $this->getWebPageUrl(
            $this->convertCategoryToUrlVariables($category)
          );

          $breadcrumbs[$url] = $category["TRANSLATIONS"]["name"];
        }
      }

      return $breadcrumbs;
    }

    /**
     * Returns formatted final URL for a specific category defined in $urlVariables.
     * If the URL for products from specific category is not configured in
     * plugin settings, uses default value.
     * 
     * @return string Final formatted relative URL
     * */
    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {
      $idCategory = (int) $urlVariables["idCategory"] ?? 0;

      if ($idCategory != 0) {
        return $this->replaceUrlVariables(
          $pluginSettings["urlForProductsInCategory"] ?? $this->defaultUrlForCategories,
          $urlVariables
        );
      } else {
        return NULL;
      }
      
    }

    public function convertCategoryToUrlVariables($category) {
      return [
        "urlizedCategoryName" => \ADIOS\Core\HelperFunctions::str2url($category["TRANSLATIONS"]["name"]),
        "idCategory" => $category['id'],
      ];
    }

    public function getCatalogInfo($idCategory = NULL, $idBrand = NULL) {
      if (self::$catalogInfo === NULL) {
        $pluginSettings = $this->websiteRenderer->getCurrentPagePluginSettings("WAI/Product/Catalog");
        $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

        $defaultItemsPerPage = (int) ($pluginSettings["defaultItemsPerPage"] ? $pluginSettings["defaultItemsPerPage"] : 6);

        if ($idCategory === NULL) {
          $idCategory = (int) $this->websiteRenderer->urlVariables["idCategory"] ?? 0;
        }

        if ($idBrand === NULL) {
          $idBrand = (int) $this->websiteRenderer->urlVariables["idBrand"] ?? 0;
        }

        $page = (int) trim($this->websiteRenderer->urlVariables["page"], "/");
        $itemsPerPage = (int) trim($this->websiteRenderer->urlVariables["itemsPerPage"], "/");

        if ($page < 1) $page = 1;
        if ($itemsPerPage < 1) $itemsPerPage = $defaultItemsPerPage;

        $filter = (new \Surikata\Plugins\WAI\Product\Filter($this->websiteRenderer))
          ->getFilterInfo()
        ;

        if (array_key_exists("sort", $this->websiteRenderer->urlVariables)) {
          $filter["sort"] = $this->websiteRenderer->urlVariables["sort"];
        }



        self::$catalogInfo = [];

        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->websiteRenderer->adminPanel);

        $productsQuery = $productModel->select('id');

        if ($filter['idCategory'] > 0) {
          $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->websiteRenderer->adminPanel);

          $allCategories = $productCategoryModel->translateForWeb(
            $productCategoryModel->getAllCached(),
            $languageIndex
          );

          $allSubCategories = $productCategoryModel->extractAllSubCategories($filter['idCategory'], $allCategories);
          self::$catalogInfo["category"] = $allCategories[$filter['idCategory']];
          self::$catalogInfo["category"]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables(self::$catalogInfo["category"]));

          $categoryIdsToBrowse = array_keys($allSubCategories);
          $categoryIdsToBrowse[] = $filter['idCategory'];

          $productsQuery->whereIn('id_category', $categoryIdsToBrowse);

          // Add additional categories to query
          $productCategoryAssignmentModel = new \ADIOS\Widgets\Products\Models\ProductCategoryAssignment(
            $this->websiteRenderer->adminPanel
          );
          $productIdsFromAdditionalCategories = $productCategoryAssignmentModel
            ->distinct('id_product')
            ->whereIn('id_category', $categoryIdsToBrowse)
            ->pluck('id_product')
            ->toArray()
          ;

          $productsQuery->orWhereIn('id',  $productIdsFromAdditionalCategories);

        }

        if (!empty($filter['filteredBrands'])) {
          $productsQuery->whereIn('id_brand', $filter['filteredBrands']);
        }

        self::$catalogInfo["productCount"] = $productModel->countRowsInQuery($productsQuery);

        if (array_key_exists("sort", $filter)) {
          $sortType = $filter["sort"];
          $sortDesc = strpos($sortType, "desc") !== false ? "DESC" : "ASC";
          switch ($sortType) {
            case "price":
            case "price_desc":
              $productsQuery->orderBy('sale_price', $sortDesc);
                break;
            case "title":
            case "title_desc":
              $productsQuery->orderBy('name_lang_'.$languageIndex, $sortDesc);
              break;
            case "date":
            case "date_desc":
              $productsQuery->orderBy('id', $sortDesc);
              break;
          }
        }

        $productsQuery->skip(($page - 1) * $itemsPerPage);
        $productsQuery->take($itemsPerPage);

        $productIds = $productsQuery->pluck('id')->toArray();

        self::$catalogInfo["products"] = $productModel->getDetailedInfoForListOfProducts(
          $productIds,
          $languageIndex
        );

        self::$catalogInfo["products"] = $productModel->addPriceInfoForListOfProducts(self::$catalogInfo["products"]);

        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        foreach (self::$catalogInfo["products"] as $key => $product) {
          self::$catalogInfo["products"][$key]["url"] =
            $productDetailPlugin->getWebPageUrl($product)
          ;
        }

        self::$catalogInfo["page"] = $page;
        self::$catalogInfo["lastPage"] = ceil((self::$catalogInfo["productCount"] / $itemsPerPage));
        self::$catalogInfo["catalogListType"] = $_COOKIE['catalogListType'] ?? 'list';

      }

      return self::$catalogInfo;

    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["sorting"] = array_key_exists("sort", $this->websiteRenderer->urlVariables) ? $this->websiteRenderer->urlVariables["sort"] : "";
      $twigParams["catalogInfo"] = $this->getCatalogInfo();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Catalog extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => "Product Catalog",
      ];
    }

    // must be the same as in Surikata\Plugins namespace
    var $defaultUrlForCategories = "{% urlizedCategoryName %}.cid.{% idCategory %}";
    var $defaultUrlForBrands = "products/brand/{% idBrand %}/{% urlizedBrandName %}";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      // Sitemap for categories
      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $pluginSettings["urlForProductsInCategory"] ?? $this->defaultUrlForCategories,
        [
          "urlizedCategoryName" => '(.+)',
          "idCategory" => '(\d+)'
        ]
      );

      // Sitemap for brands
      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $pluginSettings["urlForBrands"] ?? $this->defaultUrlForBrands,
        [
          "urlizedBrandName" => '(.+)',
          "idBrand" => '(\d+)'
        ]
      );

      return $siteMap;
    }

    public function getSettingsForWebsite() {
      return [
        "urlForProductsInCategory" => [
          "title" => "Product category URL",
          "type" => "varchar",
          "description" => "
            Relative URL for list of products in a specific category.<br/>
            Default value: {$this->defaultUrl}<br/>
          ",
        ],
        "urlForBrands" => [
          "title" => "Product brands URL",
          "type" => "varchar",
          "description" => "
            Relative URL for list of products from a specific brand.<br/>
            Default value: {$this->defaultUrl}<br/>
          ",
        ],
        "defaultItemsPerPage" => [
          "title" => "Default products per page",
          "type" => "int",
          "description" => "
            Default amount of products listed on one page.
          ",
        ],
        "showPaging" => [
          "title" => "Numeric paging",
          "type" => "boolean",
          "description" => "
            Only allow numeric paging.
          ",
        ],
      ];
    }
  }
}