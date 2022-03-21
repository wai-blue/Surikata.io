<?php

namespace Surikata\Plugins\WAI\Product {
  /**
   * Surikata\Plugins\WAI\Product\Catalog
   * 
   * Namespace: WebsiteRenderer
   * 
   * This is the default implementation of Surikata's product catalog plugin.
   * You can use own product catalog plugins in your projects but in most cases
   * this one should be enough.
   */

   class Catalog extends \Surikata\Core\Web\Plugin {

    /** The default URL pattern used for the page displaying products from a certain category */
    const DEFAULT_URL_PATTERN_FOR_CATEGORIES = "{% urlizedCategoryName %}.cid.{% idCategory %}";

    /** The default URL pattern used for the page displaying products from a certain brand */
    const DEFAULT_URL_PATTERN_FOR_BRANDS = "{% urlizedBrandName %}.bid.{% idBrand %}";

    /**
     * Cache for catalog information loaded from database. It may happen that
     * this catalog information is needed accross several plugins. This
     * static variable is loaded only once to save DB requests.
     */
    public static $catalogInfo = NULL;

    /** Similar to $catalogInfo, this is the cache for the information about
     * the product category for which is the product catalog being loaded.
     */
    public static $currentCategory = NULL;
    
    /**
     * getBreadcrumbs
     *
     * @param  mixed $urlVariables
     * @return array Information about breadcrumbs.
     */
    public function getBreadcrumbs($urlVariables = []) {
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $breadcrumbs = [];

      $categoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

      if (self::$currentCategory === NULL) {
        self::$currentCategory = 
          $urlVariables['idCategory']
            ? $categoryModel->getById($urlVariables['idCategory'])
            : NUll
        ;
      }

      if (self::$currentCategory) {
        $allCategories = $categoryModel->getAllCached();
        $allCategories = $categoryModel->translateForWeb($allCategories, $languageIndex);

        $parentCategories = array_reverse(
          $categoryModel->extractParentCategories(
            self::$currentCategory['id'], 
            $allCategories
          )
        );

        foreach ($parentCategories as $category) {
          $url = $this->getWebPageUrl(
            $this->extractUrlVariablesFromCategory($category)
          );

          $breadcrumbs[$url] = $category["TRANSLATIONS"]["name"];
        }
      }

      return $breadcrumbs;
    }

    /**
     * Returns formatted final URL for a specific category defined in $urlVariables.
     * If the URL for products from specific category is not configured in
     * plugin settings, uses default value. URL pattern used for rendering the
     * formatted URL may be different for various usages of this plugin accross the site.
     * This means that you can have more than one page using this plugin, but with
     * different plugin settings (the URL pattern). If no URL pattern is provided in
     * plugin's settings, the default pattern will be used.
     * 
     * @param array $urlVariables List of variables for which the URL should be generated.
     * @param array $pluginSettings Settings of the plugin for which the URL is generated. The generated URL will point the page where this plugin is located.
     * 
     * @return null|string NULL if no category ID is provided in the $urlVariables. Otherwise returns the formatted relative URL.
     * */
    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {
      $idCategory = (int) $urlVariables["idCategory"] ?? 0;

      if ($idCategory != 0) {
        return $this->replaceUrlVariables(
          $pluginSettings["urlForProductsInCategory"] ?? self::DEFAULT_URL_PATTERN_FOR_CATEGORIES,
          $urlVariables
        );
      } else {
        return NULL;
      }
      
    }
    
    /**
     * Extracts URL variables used in getWebpageUrlFormatted, based on the category data loaded from DB.
     * Method is used also in other plugins to help generate URL for listing products in a certain category.
     *
     * @param  array $category The category data loaded from DB.
     * @return array Array of URL variables suitable for getWebpageUrlFormatted().
     */
    public function extractUrlVariablesFromCategory($category) {
      return [
        "urlizedCategoryName" => \ADIOS\Core\HelperFunctions::str2url($category["TRANSLATIONS"]["name"]),
        "idCategory" => $category['id'],
      ];
    }
    
    /**
     * Return information about the product catalog. Uses cache to save DB requests. Returned information
     * is used in getTwigParams and passed to the TWIG templates.
     *
     * @param  null|int $idCategory If provided, only product from the category will be loaded.
     * @param  null|int $idBrand If provided, only product from the brand will be loaded.
     * @return array Information about products to be listed on the webpage.
     */
    public function getCatalogInfo($idCategory = NULL, $idBrand = NULL) {

      // Check if we have the information cached and if not, load it. Otherwise it will only be used.
      if (self::$catalogInfo === NULL) {

        // Reset the cache. Even if we will not load anything, it will be cached as an empty array.
        self::$catalogInfo = [];

        // Retrieve settings of this used in the rendered page.
        // Note: You can use this plugin more than once across the whole website. Now we need
        // the settings for the currently rendered page.
        $pluginSettings = $this->websiteRenderer->getCurrentPagePluginSettings("WAI/Product/Catalog");

        // Get the language index of currently rendered page.
        $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

        // Sanitize various input variables ...
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

        // ... End of sanitization

        // Load information about what can be filtered. This will be used e.g. in the left sidebar with the product filter.
        $filter = (new \Surikata\Plugins\WAI\Product\Filter($this->websiteRenderer))
          ->getFilterInfo()
        ;

        if (array_key_exists("sort", $this->websiteRenderer->urlVariables)) {
          $filter["sort"] = $this->websiteRenderer->urlVariables["sort"];
        }



        // Build the basic query for retrieving list of products ...
        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->websiteRenderer->adminPanel);
        $productsQuery = $productModel->select('id');

        // ... Based on the idCategory provided ...
        if ($filter['idCategory'] > 0) {
          $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->websiteRenderer->adminPanel);

          $allCategories = $productCategoryModel->translateForWeb(
            $productCategoryModel->getAllCached(),
            $languageIndex
          );

          $allSubCategories = $productCategoryModel->extractAllSubCategories($filter['idCategory'], $allCategories);
          self::$catalogInfo["category"] = $allCategories[$filter['idCategory']];
          self::$catalogInfo["category"]["url"] = $this->getWebPageUrl($this->extractUrlVariablesFromCategory(self::$catalogInfo["category"]));

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

        // ... And based on the idBrand provided.
        if (!empty($filter['filteredBrands'])) {
          $productsQuery->whereIn('id_brand', $filter['filteredBrands']);
        }

        // Count number of available products before making the query more robust.
        self::$catalogInfo["productCount"] = $productModel->countRowsInQuery($productsQuery);

        // Apply sorting based on visitor's preference.
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

        // Apply paging
        $productsQuery->skip(($page - 1) * $itemsPerPage);
        $productsQuery->take($itemsPerPage);

        // Load IDs of matching products. This will be used for further processing - extending the information about products.
        $productIds = $productsQuery->pluck('id')->toArray();

        // Get productIds from extendedFilter
        $productIds = $this->adminPanel->dispatchEventToPlugins("onProductCatalogFilterProductIds", [
          "productIds" => $productIds,
          "filter" => $filter
        ])["productIds"];

        // Add "standardized" detailed information about loaded products. This does not include price calculations.
        self::$catalogInfo["products"] = $productModel->getDetailedInfoForListOfProducts(
          $productIds,
          $languageIndex
        );

        // Add "standardized" information about prices.
        self::$catalogInfo["products"] = $productModel->addPriceInfoForListOfProducts(self::$catalogInfo["products"]);

        // Generate URLs for displaying the products.
        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        foreach (self::$catalogInfo["products"] as $key => $product) {
          self::$catalogInfo["products"][$key]["url"] =
            $productDetailPlugin->getWebPageUrl($product)
          ;
        }

        // Include some additional information to the catalog's information.
        self::$catalogInfo["page"] = $page;
        self::$catalogInfo["lastPage"] = ceil((self::$catalogInfo["productCount"] / $itemsPerPage));
        self::$catalogInfo["catalogListType"] = $_COOKIE['catalogListType'] ?? 'list';

      }

      // Return cached catalog information.
      return self::$catalogInfo;

    }
    
    /**
     * Returns information about the product catalog to be used in the TWIG template.
     *
     * @param  mixed $pluginSettings Plugin settings are only 1:1 forwared to the TWIG template.
     * @return array Information about the product catalog to be used in the TWIG template.
     */
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["sorting"] = array_key_exists("sort", $this->websiteRenderer->urlVariables) ? $this->websiteRenderer->urlVariables["sort"] : "";
      $twigParams["catalogInfo"] = $this->getCatalogInfo();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {  
  /**
   * Surikata\Plugins\WAI\Product\Catalog
   * 
   * Namespace: AdminPanel
   * 
   * This is the default implementation of Surikata's product catalog plugin.
   * You can use own product catalog plugins in your projects but in most cases
   * this one should be enough.
   */

  class Catalog extends \Surikata\Core\AdminPanel\Plugin {

    /**
     * Returns meta information about the plugin
     *
     * @return array Meta information about the plugin.
     */
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Products - Catalog"),
      ];
    }

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      // Sitemap for categories
      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $pluginSettings["urlForProductsInCategory"] ?? \Surikata\Plugins\WAI\Product\Catalog::DEFAULT_URL_PATTERN_FOR_CATEGORIES,
        [
          "urlizedCategoryName" => '(.+)',
          "idCategory" => '(\d+)'
        ]
      );

      // Sitemap for brands
      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $pluginSettings["urlForBrands"] ?? \Surikata\Plugins\WAI\Product\Catalog::DEFAULT_URL_PATTERN_FOR_BRANDS,
        [
          "urlizedBrandName" => '(.+)',
          "idBrand" => '(\d+)'
        ]
      );

      return $siteMap;
    }

    public function getSitemapXMLData() {
      $data = [];
      
      $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
      $products = $productModel->getAll();

      foreach ($products as $product) {
        $data[] = [
          'url' => $this->adios->websiteRenderer->getPlugin("WAI/Product/Detail")->getWebpageUrl($product),
        ];
      }

      return $data;
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