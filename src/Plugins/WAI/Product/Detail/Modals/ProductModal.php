<?php

namespace Surikata\Plugins\WAI\Product\Detail\Modals;

class ProductModal
{
  private $adios;

  public function __construct(&$adios = NULL) {
    $this->adios = $adios;
  }

  public function renderDefaultModal($product) {

    $priceString = number_format($product['sale_price_incl_vat_cached'], 2, ",", " ");;
    $params["uploaded_file_url"] = $this->adios->adminPanel->config["upload_url"];
    $params["rootUrl"] = $this->adios->adminPanel->websiteRenderer->rootUrl;
    $params["product"] = $product;

    $params["priceString"] = $priceString;

    return $this->adios->twig->render(
      "{$this->adios->twigTemplatesSubDir}/Plugins/WAI/Product/Modal/SimpleModal.twig",
      $params
    );

  }
}