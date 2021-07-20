<?php

namespace Surikata\Plugins\WAI\Product {
  class Catalog extends \Surikata\Core\Web\Plugin {
    var $catalogInfo = NULL;

    // must be the same as in ADIOS\Plugins namespace
    var $defaultUrl = "{% urlizedCategoryName %}.cid.{% idProductCategory %}{% page %}{% itemsPerPage %}";

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
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      return [
        "urlizedCategoryName" => \ADIOS\Core\HelperFunctions::str2url($category["name_lang_{$languageIndex}"]),
        "idProductCategory" => $category['id'],
      ];
    }

    public function getCatalogInfo($idProductCategory = NULL, $page = NULL, $itemsPerPage = NULL, $filter = NULL) {

      $pluginSettings = $this->websiteRenderer->getCurrentPagePluginSettings("WAI/Product/Catalog");
      $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

      $defaultItemsPerPage = (int) ($pluginSettings["defaultItemsPerPage"] ? $pluginSettings["defaultItemsPerPage"] : 6);

      if ($this->catalogInfo === NULL) {
        if ($idProductCategory === NULL) {
          $idProductCategory = (int) $this->websiteRenderer->urlVariables["idProductCategory"] ?? 0;
        }

        if ($page === NULL) {
          $page = (int) trim($this->websiteRenderer->urlVariables["page"], "/");
        }

        if ($page < 1) $page = 1;

        if ($itemsPerPage === NULL) {
          $itemsPerPage = (int) trim($this->websiteRenderer->urlVariables["itemsPerPage"], "/");
        }

        if ($itemsPerPage < 1) {
          $itemsPerPage = $defaultItemsPerPage;
        }

        if ($page === NULL) {
          $page = (int) trim($this->websiteRenderer->urlVariables["page"], "/");
        }

        $filter = (new \Surikata\Plugins\WAI\Product\Filter($this->websiteRenderer))
          ->getFilterInfo()
        ;

        if (array_key_exists("sort", $this->websiteRenderer->urlVariables)) {
          $filter["sort"] = $this->websiteRenderer->urlVariables["sort"];
        }

        $this->catalogInfo = $this->adminPanel
          ->getModel("Widgets/Products/Models/ProductCategory")
          ->getCatalogInfo($idProductCategory, $page, $itemsPerPage, $filter, $languageIndex)
        ;

        foreach ($this->catalogInfo["allCategories"] as $key => $category) {
          $this->catalogInfo["allCategories"][$key]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables($category));
        }
        foreach ($this->catalogInfo["allSubCategories"] as $key => $category) {
          $this->catalogInfo["allSubCategories"][$key]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables($category));
        }
        foreach ($this->catalogInfo["allSubCategories"] as $key => $category) {
          $this->catalogInfo["directSubCategories"][$key]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables($category));
        }

        $this->catalogInfo["category"]["url"] = $this->getWebPageUrl($this->convertCategoryToUrlVariables($this->catalogInfo["category"]));

        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        foreach ($this->catalogInfo["products"] as $key => $product) {
          $this->catalogInfo["products"][$key]["url"] =
            $productDetailPlugin->getWebPageUrl($product)
          ;
        }

        $this->catalogInfo["page"] = $page;
        $this->catalogInfo["lastPage"] = ceil(($this->catalogInfo["productCount"] / $itemsPerPage));
        $this->catalogInfo["catalogListType"] = $_COOKIE['catalogListType'] ?? 'list';
      }

      return $this->catalogInfo;

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
    var $defaultUrl = "{% urlizedCategoryName %}.cid.{% idProductCategory %}{% page %}{% itemsPerPage %}";

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
          "idProductCategory" => '(\d+)',
          "page" => '/?(\d+)?',
          "itemsPerPage" => '/?(\d+)?',
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