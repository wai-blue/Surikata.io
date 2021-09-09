<?php

namespace Surikata\Plugins\WAI\Misc {
  class WebsiteSearch extends \Surikata\Core\Web\Plugin {

    private $searchableFields;

    public function getSearchableFields($model = null) {
      if (is_null($model)) {
        return $this->searchableFields;
      }
      return $this->searchableFields[$model];
    }

    public function setSearchableFields($params) {
      $models = [
        "Product" => "searchInProducts",
        "ProductCategory" => "searchInProductCategories",
        "Blog" => "searchInBlogs",
      ];
      $this->searchableFields = array();
      foreach ($models as $model => $key) {
        $fields = explode(",",$params[$key]);
        $this->searchableFields[$model] = $fields;
      }
    }

    public function setWhereOrClausule($query, $model, $fieldSuffix, $searchValue) {
      foreach ($this->getSearchableFields($model) as $key => $searchableField) {
        if ($key === 0) {
          $query->where($searchableField.$fieldSuffix, 'like', '%' . $searchValue . '%');
        }
        else {
          $query->orWhere($searchableField.$fieldSuffix, 'like', '%' . $searchValue . '%');
        }
      }
      return $query;
    }

    public function renderJSON() {
      $returnArray = array();

      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);
      // action tag for later use
      $action = $this->websiteRenderer->urlVariables['action'] ?? "";
      $searchValue = $this->websiteRenderer->urlVariables['value'] ?? "";
      $searchValue = htmlspecialchars($searchValue);

      // ADD rootUrl to the URL
      $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
      $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);
      $blogDetailPlugin = new \Surikata\Plugins\WAI\Blog\Detail($this->websiteRenderer);

      // Search in products
      $productModel = $this->adminPanel
        ->getModel("Widgets/Products/Models/Product");
      $productsQuery = $productModel->getQuery();
      $productsQuery->select("*");
      $productsQuery->where('name_lang_'.$languageIndex, 'like', '%' . $searchValue . '%');
      $products = $productModel->fetchQueryAsArray($productsQuery); // TODO: UPPERCASE LOOKUP
      foreach ($products as $product) {
        $returnArray[] = [
          "category" => "products",
          "label" => $product["name_lang_".$languageIndex],
          "value" => $product["name_lang_".$languageIndex],
          "url" => "./".$productDetailPlugin->getWebPageUrl($product)
        ];
      }

      // Search in Categories
      $productCategoryModel = $this->adminPanel
        ->getModel("Widgets/Products/Models/ProductCategory");
      $productCategoriesQuery = $productCategoryModel->getQuery();
      $productCategoriesQuery->select("*");
      $productCategoriesQuery->where('name_lang_'.$languageIndex, 'like', '%' . $searchValue . '%');
      $categories = $productCategoryModel->fetchQueryAsArray($productCategoriesQuery); // TODO: UPPERCASE LOOKUP
      foreach ($categories as $category) {
        $returnArray[] = [
          "category" => "product categories",
          "label" => $category["name_lang_".$languageIndex],
          "value" => $category["code"],
          "url" => "./".$productCatalogPlugin->getWebPageUrl($productCatalogPlugin->convertCategoryToUrlVariables($category))
        ];
      }

      // Search in Websites

      // Search in Blogs
      $blogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel);
      $blogQuery = $blogModel->getQuery();
      $blogQuery->select("*");
      $blogQuery->where('name', 'like', '%' . $searchValue . '%');
      $blogQuery->orWhere('content', 'like', '%' . $searchValue . '%');
      $blogs = $blogModel->fetchQueryAsArray($blogQuery); // TODO: UPPERCASE LOOKUP
      foreach ($blogs as $blog) {
        $returnArray[] = [
          "category" => "blogs",
          "label" => $blog["name"],
          "value" => $blog["name"],
          "url" => "./".$blogDetailPlugin->getWebPageUrl($blog)
        ];
      }

      if (count($returnArray) == 0) {
        $returnArray[] = ["category" => "we did not find",
          "label" => "",
          "value" => ""
        ];
      }

      return $returnArray;

    }

    public function websiteSearch() {
      // Fulltext search across website
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);
      $returnArray = array();

      if (isset($_GET["search"])) {

        $searchValue = $this->websiteRenderer->urlVariables['search'] ?? "";
        $searchValue = htmlspecialchars($searchValue);

        // ADD rootUrl to the URL
        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);
        $blogDetailPlugin = new \Surikata\Plugins\WAI\Blog\Detail($this->websiteRenderer);

        // Search in products
        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adminPanel);

        $productsQuery = $productModel->getQuery();
        $productsQuery->select("*");
        $productsQuery = $this->setWhereOrClausule($productsQuery, "Product", "_".$languageIndex, $searchValue);
        $products = $productModel->fetchQueryAsArray($productsQuery); // TODO: UPPERCASE LOOKUP
        if (count($products) > 0) {
          $returnArray[] = [
            "model" => "Divider",
            "data" => "Products",
            "count" => count($products),
          ];
        }
        foreach ($products as $product) {
          $product["url"] = $productDetailPlugin->getWebPageUrl($product); // TODO: UPPERCASE LOOKUP
          $product['price'] = $this->adminPanel
            ->getModel("Widgets/Products/Models/Product")
            ->getPriceInfoForSingleProduct($product["id"])
          ;
          $returnArray[] = [
            "model" => "Product",
            "data" => $product
          ];
        }

        // Search in Categories
        $productCategoryModel = $this->adminPanel
          ->getModel("Widgets/Products/Models/ProductCategory");
        $productCategoriesQuery = $productCategoryModel->getQuery();
        $productCategoriesQuery->select("*");

        $productCategoriesQuery = $this->setWhereOrClausule($productCategoriesQuery, "ProductCategory", "_".$languageIndex, $searchValue);

        $categories = $productCategoryModel->fetchQueryAsArray($productCategoriesQuery); // TODO: UPPERCASE LOOKUP
        if (count($categories) > 0) {
          $returnArray[] = [
            "model" => "Divider",
            "data" => "Product Categories",
            "count" => count($categories),
          ];
        }
        foreach ($categories as $category) {
          $category["additional_info"] = $productCatalogPlugin->getCatalogInfo($category["id"]);
          $returnArray[] = [
            "model" => "ProductCategory",
            "data" => $category
          ];
        }

        // Search in Blogs
        $blogModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\Blog($this->adminPanel);
        $blogQuery = $blogModel->getQuery();
        $blogQuery->select("*");

        $blogQuery = $this->setWhereOrClausule($blogQuery, "Blog", "", $searchValue);

        $blogs = $blogModel->fetchQueryAsArray($blogQuery); // TODO: UPPERCASE LOOKUP
        if (count($blogs) > 0) {
          $returnArray[] = [
            "model" => "Divider",
            "data" => "Blogs",
            "count" => count($blogs),
          ];
        }
        foreach ($blogs as $blog) {
          $blog["url"] = $blogDetailPlugin->getWebPageUrl($blog);
          $returnArray[] = [
            "model" => "Blog",
            "data" => $blog
          ];
        }

        // Search in Webpages
        /*
        $pageModel = $this->adminPanel
          ->getModel("Widgets/Website/Models/WebPage");
        $pageQuery = $pageModel->getQuery();
        $pageQuery->select("*");
        $pageQuery->where('name', 'like', '%' . $searchValue . '%');
        $pageQuery->orWhere('url', 'like', '%' . $searchValue . '%');
        $pageQuery->orWhere('content_structure', 'like', '%' . $searchValue . '%');
        $pages = $pageModel->fetchQueryAsArray($pageQuery);
        if (count($pages) > 0) {
          $returnArray[] = [
            "model" => "Divider",
            "data" => "Pages",
            "count" => count($pages),
          ];
        }
        foreach ($pages as $page) {
          $returnArray[] = [
            "model" => "WebPage",
            "data" => $page
          ];
        }
      */

      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $this->setSearchableFields($twigParams);
      $twigParams["results"] = $this->websiteSearch();
      return $twigParams;
    }

  }
}

namespace ADIOS\Plugins\WAI\Misc {
  class WebsiteSearch extends \Surikata\Core\AdminPanel\Plugin {

    public function getSettingsForWebsite() {
      return [
        "heading" => [
          "title" => "Heading",
          "type" => "varchar",
        ],
        "numberOfResults" => [
          "title" => "Number of results to show",
          "type" => "int",
        ],
        "searchInProducts" => [
          "title" => "Searchable fields in products model",
          "type" => "varchar",
        ],
        "searchInProductCategories" => [
          "title" => "Searchable fields in category model",
          "type" => "varchar",
        ],
        "searchInBlogs" => [
          "title" => "Searchable fields in blogs model",
          "type" => "varchar",
        ],
      ];
    }

  }
}