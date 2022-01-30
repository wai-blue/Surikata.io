<?php

namespace Surikata\Plugins\WAI\Common {

  class Navigation extends \Surikata\Core\Web\Plugin {

    var $navigationItems = NULL;

    public static $allCategories = [];
    public static $flatMenuItems = [];
    public static $twigParams = NULL;

    private function convertFlatMenuItemsToTree(&$flatMenuItems, $idParent = 0) {
      $treeItems = [];
      foreach ($flatMenuItems as $item) {
        if ((int) $item['id_parent'] == (int) $idParent) {
          $children = $this->convertFlatMenuItemsToTree($flatMenuItems, $item['id']);

          $treeItem = [
            "text" => $item['title'],
            "url" => (strpos($item['url'], "://") === FALSE ? "{$this->websiteRenderer->rootUrl}/{$item['url']}" : $item['url']),
            "dropdownDirection" => ($item['expand_product_categories'] ? "left" : ""),
          ];

          if ($item['expand_product_categories']) {
            $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];

            $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);
            $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);

            $productCategories = $productCategoryModel
              ->where('id_parent', '=', 0)
              ->orWhereNull('id_parent')
              ->get()
              ->toArray()
            ;

            $productCategories = $productCategoryModel->translateForWeb(
              $productCategories,
              $languageIndex
            );

            foreach ($productCategories as $key => $value) {
              $productCategories[$key]["title"] = $value["TRANSLATIONS"]["name"];
              $productCategories[$key]["url"] = $productCatalogPlugin->getWebPageUrl(
                $productCatalogPlugin->extractUrlVariablesFromCategory($value)
              );
            }

            $treeItem["children"] = $this->convertFlatMenuItemsToTree($productCategories);
          } else if (count($children) > 0) {
            $treeItem["children"] = $children;
          }

          $treeItems[] = $treeItem;
        }
      }

      return $treeItems;
    }

    public function getMenuItems() {
      if (empty(self::$flatMenuItems)) {
        $navigationPluginSettings = 
          $this->websiteRenderer->getCurrentPagePluginSettings(
            "WAI/Common/Navigation"
          )
        ;

        self::$flatMenuItems =
          (new \ADIOS\Widgets\Website\Models\WebMenuItem($this->adminPanel))
          ->getByIdMenu((int) $navigationPluginSettings['menuId'] ?? 0)
        ;
      }

      return self::$flatMenuItems;
    }

    public function getLanguages() {
      $returnArray = [];
      $languages = $this->websiteRenderer->adminPanel->config['widgets']['Website']['domainLanguages'];
      $domains = $this->websiteRenderer->getAvailableDomains();

      foreach ($languages as $languageIndex => $language) {
        $returnArray[$languageIndex] = [];
        $returnArray[$languageIndex]["language"] = $language;
        foreach ($domains as $key => $domain) {
          if ($domain["languageIndex"] == $languageIndex) {
            $returnArray[$languageIndex]["languageCode"] = $key;
            break;
          }
        }
      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {
      if (self::$twigParams === NULL) {
        $twigParams = $pluginSettings;
        $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

        $twigParams["languages"] = $this->getLanguages();

        $this->websiteRenderer->logTimestamp("Navigation getTWigParams #1");

        if ($this->navigationItems === NULL) {
          $this->websiteRenderer->logTimestamp("Navigation getTWigParams #1.1");

          self::$flatMenuItems = $this->getMenuItems();

          $this->websiteRenderer->logTimestamp("Navigation getTWigParams #1.2");

          $this->navigationItems = $this->convertFlatMenuItemsToTree(
            self::$flatMenuItems
          );

          $this->websiteRenderer->logTimestamp("Navigation getTWigParams #1.3");
        }

        $this->websiteRenderer->logTimestamp("Navigation getTWigParams #2");

        // navigationItems
        $twigParams["navigationItems"] = $this->navigationItems;

        // cartContents
        $twigParams["cartContents"] = (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))->getCartContents();

        if ($pluginSettings["showCategories"]) {
          if (empty(self::$allCategories)) {
            $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);
            $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);

            self::$allCategories = $productCategoryModel->orderBy('order_index')->get()->toArray();

            self::$allCategories = $productCategoryModel->translateForWeb(self::$allCategories, $languageIndex);

            foreach (self::$allCategories as $key => $category) {
              $url = $productCatalogPlugin->getWebPageUrl($productCatalogPlugin->extractUrlVariablesFromCategory($category));
              self::$allCategories[$key]["url"] = $this->websiteRenderer->rootUrl ."/". $url;
            }
          }

          $categoryTree = $productCategoryModel->getAllCategoriesAndSubCategories(self::$allCategories);
          $twigParams["categories"] = $categoryTree;
        }

        $this->websiteRenderer->logTimestamp("Navigation getTWigParams #3");

        // homePageUrl redirect
        $twigParams['homePageUrl'] = (new \ADIOS\Widgets\Website\Models\WebRedirect($this->adminPanel))
          ->getHomePageUrl()
        ;

        // breadcrumbs (in some templates it is better to render breadcrumbs within Navigation plugin)
        $twigParams['breadcrumbs'] = (new \Surikata\Plugins\WAI\Common\Breadcrumb($this->websiteRenderer))->getBreadcrumbsUrl();

        $this->websiteRenderer->logTimestamp("Navigation getTWigParams #4");

        self::$twigParams = $twigParams;
      }

      return self::$twigParams;
    }
  }

}


namespace ADIOS\Plugins\WAI\Common {

  class Navigation extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("Main website navigation"),
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "menuId" => [
          "title" => "Ponuka menu",
          "type" => "int",
          "enum_values" => (new \ADIOS\Widgets\Website\Models\WebMenu($this->adios))
            ->getEnumValues()
          ,
        ],
        "slogan" => [
          "title" => "Short slogan",
          "type" => "varchar",
        ],
        "shortContact" => [
          "title" => "Short contact info in header",
          "type" => "varchar",
        ],
        "homepageUrl" => [
          "title" => "Homepage URL",
          "type" => "varchar",
        ],
        "showCategories" => [
          "title" => "Show categories",
          "type" => "bool",
        ],
      ];
    }
    
  }
}