<?php

namespace Surikata\Plugins\WAI\Export\Heureka\Controllers;

use \ADIOS\Widgets\Products\Models\Product;
use \Surikata\Plugins\WAI\Product\Detail;

class ProductsXMLGenerator extends \Surikata\Core\Web\Controller {
  public function render() {
    $uploadUrl = $this->websiteRenderer->adminPanel->config['files_url'];
    $deliveryDefaults = $this->websiteRenderer->adminPanel->config["settings"]["delivery"]["defaults"];
    $deliveryDay = $deliveryDefaults['deliveryDay'] ?? 0;
    
    $productModel = new Product($this->websiteRenderer->adminPanel);
    $heurekaPlugin = new \ADIOS\Plugins\WAI\Export\Heureka($this->websiteRenderer);
    $productDetailPlugin = new Detail($this->websiteRenderer);
    $deliveryPlugins = $this->websiteRenderer->getDeliveryPlugins();

    $products = $productModel->getAll();
    $idProducts = [];
    foreach ($products as $product) {
      $idProducts[] = $product['id'];
    }

    $products = $productModel->getDetailedInfoForListOfProducts($idProducts);

    $xml = '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= "<SHOP>";

    foreach ($products as $product) {
      $productUrl = $productDetailPlugin->getWebPageUrl($product);
      $deliveryDate = ((int) $product['delivery_day'] == 0 ? $deliveryDay : $product['delivery_day']);

      $xml .= "<SHOPITEM>\n";
      $xml .= "  <ITEMGROUP_ID>".$heurekaPlugin->getHeurekaProductId($product)."</ITEMGROUP_ID>\n"; // varianty produktov este nie su podporovane
      $xml .= "  <ITEM_ID>".$heurekaPlugin->getHeurekaProductId($product)."</ITEM_ID>\n"; // varianty produktov este nie su podporovane
      $xml .= "  <PRODUCTNAME>{$product['name_lang_1']}</PRODUCTNAME>\n";
      $xml .= "  <PRODUCT>{$product['name_lang_1']}</PRODUCT>\n";
      $xml .= "  <DESCRIPTION>{$product['description_lang_1']}</DESCRIPTION>\n";
      $xml .= "  <URL>https://".$_SERVER['HTTP_HOST'].WEBSITE_REWRITE_BASE."{$productUrl}</URL>\n";
      $xml .= "  <IMGURL>{$uploadUrl}/{$product['image']}</IMGURL>\n";
      $xml .= "  <PRICE_VAT>".number_format($product['PRICE']['fullPrice'], 2, ",", "")."</PRICE_VAT>\n";
      $xml .= "  <MANUFACTURER>{$product['BRAND']['name']}</MANUFACTURER>\n";
      $xml .= "  <EAN>{$product['ean']}</EAN>\n";
      $xml .= "  <PRODUCTNO>{$product['number']}</PRODUCTNO>\n";
      $xml .= "  <GIFT>{$product['gitf_lang_1']}</GIFT>\n";
      $xml .= "  <DELIVERY_DATE>".($deliveryDate > 0 ? $deliveryDate : "")."</DELIVERY_DATE>\n";

      foreach ($product['ACCESSORIES'] as $accessory) {
        $xml .= "  <ACCESSORY>".$heurekaPlugin->getHeurekaProductId($accessory)."</ACCESSORY>\n";
      }

      $i = 0;
      foreach ($product['SERVICES'] as $service) {
        $xml .= "  <SPECIAL_SERVICE>{$service['name_lang_1']}</SPECIAL_SERVICE>\n";
        if ($i++ == 4) break; // Heureka feed moze mat max 5 special services
      }

      if ((int) $product['extended_warranty'] > 0) {
        $xml .= "  <EXTENDED_WARRANTY>{$product['extended_warranty']}</EXTENDED_WARRANTY>\n";
      }

      foreach ($deliveryPlugins as $plugin) {
        $deliveryPrice = $plugin->calculatePriceForProduct($product);

        $xml .= "  <DELIVERY>\n";
        $xml .= "    <DELIVERY_ID>{$plugin->heurekaId}</DELIVERY_ID>\n";
        $xml .= "    <DELIVERY_PRICE>".number_format($deliveryPrice, 2, ",", "")."</DELIVERY_PRICE>\n";
        $xml .= "  </DELIVERY>\n";
      }

      $xml .= "</SHOPITEM>";
      $xml .= "\n";

    }

    $xml .= "</SHOP>";

    // file_put_contents(__DIR__."/test.xml", $xml);
    
    return $xml;
  }
}