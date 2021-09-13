<?php

namespace Surikata\Plugins\WAI\Product {
  use ADIOS\Widgets\Products\Models\Service;
  class Detail extends \Surikata\Core\Web\Plugin {
    var $productInfo = NULL;
    var $deleteCurrentPageBreadCrumb = true;

    public function getBreadCrumbs($urlVariables = []) {
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $productInfo = $this->getProductInfo();
      $productInfo['idProductCategory'] = $productInfo['id_category'];

      $productCatalog = 
        new \Surikata\Plugins\WAI\Product\Catalog(
          $this->websiteRenderer
        )
      ;

      $productCatalogUrl = $productCatalog->getWebPageUrl();

      $breadCrumb = 
        new \Surikata\Plugins\WAI\Common\Breadcrumb(
          $this->websiteRenderer
        )
      ;

      $breadCrumbs = 
        $breadCrumb->getMenuBreadCrumbs(
          $productCatalogUrl, 
          true
        )
      ;

      $breadCrumbs = array_merge(
        $breadCrumbs, 
        $productCatalog->getBreadCrumbs($productInfo)
      );

      // REVIEW: toto "$productInfo["name_lang_{$languageIndex}"];" som
      // opravil na $productInfo["TRANSLATIONS"]["name"];
      // Treba podobnu opravu spravit aj na ostatnych miestach.
      $breadCrumbs[
        $this->getWebPageUrlFormatted($productInfo)
      ] = $productInfo["TRANSLATIONS"]["name"];

      return $breadCrumbs;
    }

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = []) {
      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $productName = $urlVariables["name_lang_{$languageIndex}"] ?? "";
      $idProduct = (int) $urlVariables["id"] ?? 0;
      return \ADIOS\Core\HelperFunctions::str2url($productName).".pid.{$idProduct}";
    }

    function getProductInfo() {
      if ($this->productInfo === NULL) {

        $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adminPanel);
        $productCategoryModel = new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel);

        $this->productInfo = $productModel
          ->getById((int) $this->websiteRenderer->urlVariables['idProduct'])
        ;

        $this->productInfo = $productModel->translateProductForWeb($this->productInfo, $languageIndex);

        $allCategories = (new \ADIOS\Widgets\Products\Models\ProductCategory($this->adminPanel))->getAll(); // TODO: UPPERCASE LOOKUP

        // REVIEW: atribut 'prislusenstvo' prelozit na 'accesories'
        // REVIEW: nemal by "vypocet" URL adresy ist do nejakej separatnej funkcie?
        foreach ($this->productInfo['prislusenstvo'] as $key => $value) {
          $this->productInfo['prislusenstvo'][$key]['url'] =
            \ADIOS\Core\HelperFunctions::str2url($value['TRANSLATIONS']['name'])
            .".pid.{$value['id']}"
          ;
        }

        foreach ($this->productInfo['related'] as $key => $value) {
          $this->productInfo['related'][$key]['url'] =
            \ADIOS\Core\HelperFunctions::str2url($value['TRANSLATIONS']['name'])
            .".pid.{$value['id']}"
          ;
        }

        $this->productInfo['priceInfo'] = $productModel
          ->getPriceInfoForSingleProduct((int) $this->websiteRenderer->urlVariables['idProduct'])
        ;

        $this->productInfo['breadcrumbs'] = $productCategoryModel
          ->breadcrumbs((int) $this->productInfo['id_category'], $allCategories)
        ;
      }

      $allUnits = (new \ADIOS\Widgets\Settings\Models\Unit($this->adminPanel))->getAll();
      foreach ($allUnits as $unit) {
        if ($this->productInfo["id_delivery_unit"] == $unit["id"]) {
          $this->productInfo["DELIVERY_UNIT"] = $unit;
          break;
        }
      }
      foreach ($this->productInfo['FEATURES'] as $key => $feature) {
        foreach ($allUnits as $unit) {
          if ($feature["id_measurement_unit"] == $unit["id"]) {
            $this->productInfo['FEATURES'][$key]["MEASUREMENT_UNIT"] = $unit;
            break;
          }
        }
      }
      return $this->productInfo;
    }

    public function getServices() {

      /** @var Service $serviceModel */
      $serviceModel = $this->adminPanel
        ->getModel("Widgets/Products/Models/Service")
      ;

      /** @var array $services */
      $services = $serviceModel
        ->getAll()
      ;
      return $services;
    }
    
    public function renderJSON() {
      $returnArray = [];
      $productAction = $this->websiteRenderer->urlVariables['productAction'] ?? "";

      switch ($productAction) {
        case "getQuickView":
          $product = $this->getProductInfo();
          $returnArray["product"] = [];
          $returnArray["product"] = $product;
          $returnArray["productModalContent"] = (new \Surikata\Plugins\WAI\Product\Detail\Modals\ProductModal($this->websiteRenderer))
            ->renderDefaultModal($product)
          ;
          break;
      }

      return $returnArray;
    }

    public function getTwigParams($pluginSettings) {

      $customerUID = $this->websiteRenderer->getCustomerUID();
      $idProduct = (int) $this->websiteRenderer->urlVariables['idProduct'];

      // save datetime of render
      //$this->adminPanel
     //   ->getModel("Widgets/Customers/Models/CustomerProduktPrezerany")
     //   ->logActivityByCustomerUID($customerUID, $idProduct)
     // ;

      $twigParams = $pluginSettings;

      $twigParams["services"] = $this->getServices();
      $twigParams["productInfo"] = (new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer))->getProductInfo();

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Detail extends \Surikata\Core\AdminPanel\Plugin {

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {
      return [
        $webPageUrl . '(.+).pid.(\d+)' => [
          1 => "name",
          2 => "idProduct",
        ],
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "show_accessories" => [
          "title" => "Show accessories for products",
          "type" => "boolean",
        ],
        "show_similar_products" => [
          "title" => "Zobraziť podobné produkty",
          "type" => "boolean",
        ],
      ];
    }

    public function onModelAfterFormParams($event) {
      $data = $event["data"];

      if ($event["model"]->name == "Widgets/Products/Models/Product") {
        $productUrl = $this->adios->websiteRenderer->getPlugin("WAI/Product/Detail")->getWebpageUrl($event["data"]);
        // REVIEW: Este jednu vec som si uvedomil.
        // $adios->config["language"] nie je to iste, ako jazyky v "domains".
        // To bude treba opravit, zatial ponechame tento komentar ako pripomienku.
        $event["params"]["template"]["columns"][1]["rows"][2]["html"] .= "
          <a class='btn btn-icon-split btn-light' target='_blank' href='{$this->adios->websiteRenderer->rootUrl}/{$this->adios->config["language"]}/{$productUrl}'>
            <span class='icon'><i class='fa fa-link'></i></span>
            <span class='text'>Open product page</span>
          </a>"
        ;
      }

      return $event;
    }
  }
}