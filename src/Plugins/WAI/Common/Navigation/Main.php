<?php

namespace Surikata\Plugins\WAI\Common {

  class Navigation extends \Surikata\Core\Web\Plugin {

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
            foreach ($productCategories as $key => $value) {
              $productCategories[$key]["title"] = $value["name_lang_{$languageIndex}"];
              $productCategories[$key]["url"] = $productCatalogPlugin->getWebPageUrl(
                $productCatalogPlugin->convertCategoryToUrlVariables($value)
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

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      // navigationItems
      $twigParams["navigationItems"] = $this->convertFlatMenuItemsToTree(
        (new \ADIOS\Widgets\Website\Models\WebMenuItem($this->adminPanel))
          ->getByIdMenu((int) $pluginSettings['menuId'] ?? 0)
      );

      // cartContents
      $twigParams["cartContents"] = (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))->getCartContents();

      return $twigParams;
    }
  }

}


namespace ADIOS\Plugins\WAI\Common {

  class Navigation extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "menuId" => [
          "title" => "Ponuka menu",
          "type" => "int",
          "enum_values" => (new \ADIOS\Widgets\Website\Models\WebMenu($this->adios))
            ->getEnumValues()
          ,
        ],
      ];
    }
    
  }
}