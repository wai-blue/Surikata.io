<?php

namespace Surikata\Plugins\WAI\Product {
  class Catalog extends \Surikata\Core\Web\Plugin {
    public static $catalogInfo = NULL;
    var $currentCategory = NULL;

    // must be the same as in ADIOS\Plugins namespace
    var $defaultUrl = "{% urlizedCategoryName %}.cid.{% idProductCategory %}";

    public function getBreadCrumbs($urlVariables = []) {
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $breadCrumbs = [];

      $categoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

      if ($this->currentCategory === NULL) {
        $this->currentCategory = 
          $urlVariables['idProductCategory'] 
            ? $categoryModel->getById($urlVariables['idProductCategory'])
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

          $breadCrumbs[$url] = $category["TRANSLATIONS"]["name"];
        }
      }

      return $breadCrumbs;
    }

    /**
     * Returns formatted final URL for a specific category defined in $urlVariables.
     * If the URL for products from specific category is not configured in
     * plugin settings, uses default value.
     * 
     * @return string Final formatted relative URL
     * */
    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = []) {
      $urlizedCategoryName = $urlVariables["urlizedCategoryName"] ?? "";
      $idProductCategory = (int) $urlVariables["idProductCategory"] ?? 0;

      $url = $pluginSettings["urlForProductsInCategory"] ?? "";

      if (empty($url)) {
        $url = $this->defaultUrl;
      }

      if ($idProductCategory != 0) {
        return $this->replaceUrlVariables($url, $urlVariables);
      } else {
        return NULL;
      }
      
    }

    public function convertCategoryToUrlVariables($category) {
      return [
        "urlizedCategoryName" => \ADIOS\Core\HelperFunctions::str2url($category["TRANSLATIONS"]["name"]),
        "idProductCategory" => $category['id'],
      ];
    }

    public function getCatalogInfo($idProductCategory = NULL, $page = NULL, $itemsPerPage = NULL, $filter = NULL) {
      $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->websiteRenderer->adminPanel);

      $pluginSettings = $this->websiteRenderer->getCurrentPagePluginSettings("WAI/Product/Catalog");
      $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

      $defaultItemsPerPage = (int) ($pluginSettings["defaultItemsPerPage"] ? $pluginSettings["defaultItemsPerPage"] : 6);

      if (self::$catalogInfo === NULL) {
        if ($idProductCategory === NULL) {
          $idProductCategory = (int) $this->websiteRenderer->urlVariables["idProductCategory"] ?? 0;
        }

        if ($page === NULL) {
          $page = (int) trim($this->websiteRenderer->urlVariables["page"], "/");
        }

        if ($itemsPerPage === NULL) {
          $itemsPerPage = (int) trim($this->websiteRenderer->urlVariables["itemsPerPage"], "/");
        }

        if ($itemsPerPage < 1) {
          $itemsPerPage = $defaultItemsPerPage;
        }

        if ($page === NULL) {
          $page = (int) trim($this->websiteRenderer->urlVariables["page"], "/");
        }

        if ($page < 1) $page = 1;
        if ($itemsPerPage < 1) $itemsPerPage = 1;
        if (!is_array($filter)) $filter = [];

        $filter = (new \Surikata\Plugins\WAI\Product\Filter($this->websiteRenderer))
          ->getFilterInfo()
        ;

        if (array_key_exists("sort", $this->websiteRenderer->urlVariables)) {
          $filter["sort"] = $this->websiteRenderer->urlVariables["sort"];
        }

        // self::$catalogInfo = (new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel))
        //   ->getCatalogInfo($idProductCategory, $page, $itemsPerPage, $filter, $languageIndex)
        // ;



        self::$catalogInfo = [];

        ////////////////////////////////////////
        // info about categories

        $allCategories = $productCategoryModel->translateForWeb(
          $productCategoryModel->getAllCached(),
          $languageIndex
        );

        $allSubCategories = $productCategoryModel->extractAllSubCategories($idProductCategory, $allCategories);
        self::$catalogInfo["category"] = $allCategories[$idProductCategory];

        $categoryIdsToBrowse = array_keys($allSubCategories);
        $categoryIdsToBrowse[] = $idProductCategory;

        ////////////////////////////////////////
        // info about products

        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->websiteRenderer->adminPanel);

        $productsQuery = $productModel->getQuery();

        if ($idProductCategory > 0) {
          // not adding where condition if all products should be retreived,
          // the condition slows down the query
          $productsQuery->whereIn('id_category', $categoryIdsToBrowse);
        }

        if (!empty($filter['filteredBrands'])) {
          $productsQuery->whereIn('id_brand', $filter['filteredBrands']);
        }

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
              $productsQuery->orderBy('name_lang_1', $sortDesc);
              break;
            case "date":
            case "date_desc":
              $productsQuery->orderBy('id', $sortDesc);
              break;
          }
        }

        $allProducts = $productModel->fetchQueryAsArray($productsQuery, 'id', FALSE);

        self::$catalogInfo["productCount"] = count($allProducts);

        $productModel->addLookupsToQuery($productsQuery);
        $productsQuery->skip(($page - 1) * $itemsPerPage);
        $productsQuery->take($itemsPerPage);

        self::$catalogInfo["products"] = $productModel->fetchQueryAsArray($productsQuery, 'id', FALSE);
        self::$catalogInfo["products"] = $productModel->addPriceInfoForListOfProducts(self::$catalogInfo["products"]);
        self::$catalogInfo["products"] = $productModel->unifyProductInformationForListOfProduct(self::$catalogInfo["products"]);
        self::$catalogInfo["products"] = $productModel->translateForWeb(self::$catalogInfo["products"], $languageIndex);









        self::$catalogInfo["category"]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables(self::$catalogInfo["category"]));

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
    var $niceName = "Product Catalog";
    
    // must be the same as in Surikata\Plugins namespace
    var $defaultUrl = "{% urlizedCategoryName %}.cid.{% idProductCategory %}";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["urlForProductsInCategory"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "urlizedCategoryName" => '(.+)',
          "idProductCategory" => '(\d+)'
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