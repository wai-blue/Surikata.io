<?php

namespace ADIOS\Plugins\WAI\Export;

class Heureka extends \Surikata\Core\AdminPanel\Plugin {

  var $niceName = "Heureka XML feedy";

  public function getHeurekaProductId($product) {
    return substr(md5($product['id']), 0, 5).".".str_pad((string) $product['id'], 8, "0", STR_PAD_LEFT);
  }

  public function onAfterSiteMap($event) {
    $siteMap = $event['site_map'];
    $websiteRenderer = $event['website_renderer'];

    $siteMap["heureka-products.xml"] = [
      "controllers" => [
        new \Surikata\Plugins\WAI\Export\Heureka\Controllers\ProductsXMLGenerator($websiteRenderer),
      ],
      "template" => "Layouts/WithLeftSidebar",
    ];

    $siteMap["heureka-stock.xml"] = [
      "controllers" => [
        new \Surikata\Plugins\WAI\Export\Heureka\Controllers\StockXMLGenerator($websiteRenderer),
      ],
      "template" => "Layouts/WithLeftSidebar",
    ];

    $event['site_map'] = $siteMap;

    return $event; // forward event unchanged
  }

  public function onGeneralControllerAfterRenderPlugin($event) {
    $controller = $event["controller"];
    $renderParams = $event["renderParams"];
    $pluginName = $event["pluginName"];
    $pluginSettings = $event["pluginSettings"];
    $panelName = $event["panelName"];

    if (empty($renderParams['order'])) return $event;

    $pluginConfig = $controller->adminPanel->config["settings"]["plugins"]["WAI"]["Export"]["Heureka"];

    if (!empty($pluginConfig["secretAPIKey"])) {
      try {
        if ($pluginConfig["serverVersion"] == "sk") {
          $serverVersion = \Heureka\ShopCertification::HEUREKA_SK;
        } else {
          $serverVersion = \Heureka\ShopCertification::HEUREKA_CZ;
        }

        $shopCertification = new \Heureka\ShopCertification(
          $pluginConfig["secretAPIKey"],
          ['service' => $serverVersion]
        );

        $shopCertification->setEmail($renderParams['order']['CUSTOMER']['email']);
        $shopCertification->setOrderId((int) $renderParams['order']['number']);

        foreach ($renderParams['order']['ITEMS'] ?? [] as $item) {
          $shopCertification->addProductItemId($this->getHeurekaProductId(["id" => $item['id_product']]));
        }

        $shopCertification->logOrder();

      } catch (\Heureka\ShopCertification\Exception $e) {
        var_dump($e->getMessage());
        exit();
      }
    }

    if (
      !empty($pluginConfig["publicAPIKey"])
      && $pluginName == "WAI/Order/Confirmation"
    ) {
      $productsJs = "";
      foreach ($renderParams['order']['ITEMS'] ?? [] as $item) {
        $productsJs . "
          hrq.push([
            'addProduct',
            '".ads("{$item['product_number']} {$item['product_name']}")."',
            '".number_format($item['unit_price'], 2, ".", "")."',
            '".number_format($item['quantity'], 2, ".", "")."',
            '".$this->getHeurekaProductId(["id" => $item['id_product']])."'
          ]);
        ";
      }

      $renderParams["heurekaConversionsJS"] = "
        <script type='text/javascript'>
          var _hrq = _hrq || [];
          _hrq.push(['setKey', '{$pluginConfig["publicAPIKey"]}']);
          _hrq.push(['setOrderId', '".(int) $renderParams['order']['number']."']);
          {$productsJs}
          _hrq.push(['trackOrder']);
          (function() {
            var ho = document.createElement('script'); ho.type = 'text/javascript'; ho.async = true;
            ho.src = 'https://im9.cz/js/ext/1-roi-async.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ho, s);
          })();
        </script>
      ";
    }

    $event["renderParams"] = $renderParams;

    return $event;
  }

  public function onADIOSAfterInit($event) {
    if (!class_exists('\Heureka\ShopCertification')) {
      throw new \ADIOS\Core\Exception(
        "Plugin WAI/Export/Heureka: ShopCertification class not found. "
        ."Install it with composer. More info at https://github.com/heureka/overeno-zakazniky."
      );
    }

    return $event;
  }

  public function onADIOSBeforeActionRender($event) {
    $adios = $event["adios"];
    $pluginConfig = $adios->config["settings"]["plugins"]["WAI"]["Export"]["Heureka"];

    if (
      empty($adios->requestedURI)
      && empty($pluginConfig["secretAPIKey"])
    ) {
      $adios->userNotifications->addHtml("
        Nemáte nastavený Tajný API Kľúč pre Heureka API.
        <a
          href='javascript:void(0)'
          onclick='window_render(\"Plugins/WAI/Export/Heureka/Settings\");'
        >Nastaviť</a>
      ");
    }

    return $event;
  }

}
