<?php

namespace Surikata\Plugins\WAI\Order {
  class PaymentConfirmation extends \Surikata\Core\Web\Plugin {

    public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {

      $order = $urlVariables["order"] ?? [];
      $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adminPanel);

      $url = $pluginSettings["urlPattern"] ?? "";
      if (empty($url)) {
        $url = "order/{% orderNumber %}/{% checkCode %}/payment-received";
      }

      $url = str_replace("{% orderNumber %}", $order['number'], $url);
      $url = str_replace("{% checkCode %}", $orderModel->getCheckCode($order), $url);

      return $url;
    }

    public function getTwigParams($pluginSettings) {

      $twigParams = $pluginSettings;
      $orderNumber = (int) ($this->websiteRenderer->urlVariables["orderNumber"] ?? 0);
      $checkCode = $this->websiteRenderer->urlVariables["checkCode"] ?? "";

      $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adminPanel);
      $order = $orderModel->getByNumber($orderNumber);

      if ($orderModel->validateCheckCode($order, $checkCode)) {
        $twigParams["order"] = $order;
      }

      return $twigParams;
    }

  }
}

namespace ADIOS\Plugins\WAI\Order {
  class PaymentConfirmation extends \Surikata\Core\AdminPanel\Plugin {

    var $defaultUrl = "order/{% orderNumber %}/{% checkCode %}/payment-received";

    public function getSiteMap($pluginSettings = [], $webPageUrl = "") {

      $urlPattern = $pluginSettings["urlPattern"] ?? "";
      if (empty($urlPattern)) {
        $urlPattern = $this->defaultUrl;
      }

      $this->convertUrlPatternToSiteMap(
        $siteMap,
        $urlPattern,
        [
          "orderNumber" => '(\d+)',
          "checkCode" => '(.*?)',
        ]
      );

      return $siteMap;
    }

    public function getSettingsForWebsite() {
      return [
        "urlPattern" => [
          "title" => "Payment confirmation page URL",
          "type" => "varchar",
          "description" => "
            Relative URL for payment confirmation page.<br/>
            Default value: order/{% orderNumber %}/{% checkCode %}/payment-received
          ",
        ],
      ];
    }

    public function onOrderDetailAfterSidebarButtons($eventData) {
      $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);
      $order = $orderModel->getById($eventData['data']['id']);

      $url = (new \Surikata\Plugins\WAI\Order\PaymentConfirmation($this->adios->websiteRenderer))
        ->getWebPageUrl(["order" => $order], $order["domain"])
      ;

      $domainInfo = $this->adios->getDomainInfo($order["domain"]);

      $eventData["html"] .= "<a href='//{$domainInfo["rootUrl"]}/{$url}' target=_blank>Payment confirmation URL</a><br/>";

      return $eventData;
    }

  }
}