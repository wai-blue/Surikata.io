<?php

namespace Surikata\Plugins\WAI\Misc {

  use ADIOS\Widgets\Customers\Models\CustomerUID;
  use ADIOS\Widgets\Customers\Models\SearchQuery;

  class WebsiteSearch extends \Surikata\Core\Web\Plugin {

    private $searchableFields;
    var $defaultWebsiteSearchdUrl = [
      1 => "search",
      2 => "hladat",
      3 => "hledat"
    ];

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {

      $languageIndex = (int) $this->websiteRenderer->domain["languageIndex"];
      $url = $pluginSettings["urlPattern"] ?? $this->defaultWebsiteSearchdUrl[$languageIndex];
      return $url;

    }

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
      $action = $this->websiteRenderer->urlVariables['action'] ?? "";
      $returnArray = array();
      
      switch ($action) {
        case "searchResults":
          $returnArray = $this->searchResults();
        break;
      }

      return $returnArray;
    }

    public function logSearchQuery($query) {
      $customerUID = $this->websiteRenderer->getCustomerUID();
      $customerID = (new CustomerUID)->getByCustomerUID($customerUID);

      if ($query != '') {
        $target_url = htmlspecialchars($this->websiteRenderer->urlVariables['urlOpened'] ?? "");
        $searchQueryModel = new SearchQuery($this->adminPanel);
        $searchQueryModel->insertRow([
          "id_customer_uid" => $customerID["id"],
          "query" => $query,
          "target_url" => $target_url,
          "search_datetime" => ["sql" => "now()"],
        ]);
      }
    }

    public function searchResults() {
      // Fulltext search across website
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);
      $returnArray = array();

      $query = $this->websiteRenderer->urlVariables['q'] ?? "";

      if (strlen($query) > 3) {

        $this->logSearchQuery($query);

        // ADD rootUrl to the URL
        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        $productCatalogPlugin = new \Surikata\Plugins\WAI\Product\Catalog($this->websiteRenderer);
        $blogDetailPlugin = new \Surikata\Plugins\WAI\Blog\Detail($this->websiteRenderer);

        // Search in products
        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adminPanel);

        $productsQuery = $productModel->getQuery();
        $productsQuery->select("*");
        $productsQuery = $this->setWhereOrClausule($productsQuery, "Product", "_".$languageIndex, $query);
        $productsQuery->skip(0)->take(40);
        $products = $productModel->fetchRows($productsQuery); // TODO: UPPERCASE LOOKUP
        if (count($products) > 0) {
          $returnArray[] = [
            "model" => "Divider",
            "data" => "Products",
            "count" => count($products),
          ];
        }
        foreach ($products as $product) {
          $product["url"] = $productDetailPlugin->getWebPageUrl($product); // TODO: UPPERCASE LOOKUP
          $product['PRICE'] = $this->adminPanel
            ->getModel("Widgets/Products/Models/Product")
            ->getPriceInfoForSingleProduct($product["id"])
          ;
          $product = $productModel->translateSingleProductForWeb($product, $languageIndex);
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

        $productCategoriesQuery = $this->setWhereOrClausule($productCategoriesQuery, "ProductCategory", "_".$languageIndex, $query);
        $productCategoriesQuery->skip(0)->take(20);

        $categories = $productCategoryModel->fetchRows($productCategoriesQuery); // TODO: UPPERCASE LOOKUP
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

        $blogQuery = $this->setWhereOrClausule($blogQuery, "Blog", "", $query);
        $blogQuery->skip(0)->take(40);

        $blogs = $blogModel->fetchRows($blogQuery); // TODO: UPPERCASE LOOKUP
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
      }

      if (count($returnArray) === 0) {
        $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adminPanel);
        $productDetailPlugin = new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer);
        $filters = ["recommended", "on_sale"];
        $take = 6;
        $productSneakPeek = [];

        foreach ($filters as $filter) {
          switch ($filter) {
            case "recommended":
              $productQuery = $productModel
                ->where("is_recommended", TRUE)
                ->skip(0)->take($take);
              break;
            case "on_sale":
              $productQuery = $productModel
                ->where("is_on_sale", TRUE)
                ->skip(0)->take($take);
              break;
          }

          $products = $productModel->fetchRows($productQuery);
          foreach ($products as $key => $product) {
            $product["url"] = $productDetailPlugin->getWebPageUrl($product);
            $product['PRICE'] = $this->adminPanel
              ->getModel("Widgets/Products/Models/Product")
              ->getPriceInfoForSingleProduct($product["id"])
            ;
            $product = $productModel->translateSingleProductForWeb($product, $languageIndex);
            $products[$key] = $product;
          }
          $productSneakPeek[$filter] = $products;
        }
        $returnArray["productsNoSearch"] = $productSneakPeek;
      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $this->setSearchableFields($twigParams);
      $twigParams["results"] = $this->searchResults();

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