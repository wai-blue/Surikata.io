<?php

namespace Surikata\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\Web\Plugin {
    var $cartContents = NULL;

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $userProfileController = new \Surikata\Core\Web\Controllers\UserProfile($this->websiteRenderer);
      $userProfileController->reloadUserProfile();

      $twigParams['userLogged'] = $this->websiteRenderer->userLogged;

      $twigParams["cartContents"] = (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))->getCartContents();

      $twigParams["paymentMethods"] = [];
      $paymentServiceModel = new \ADIOS\Widgets\Shipping\Models\PaymentService($this->adminPanel);

      foreach ($paymentServiceModel->getAll() as $payment) {
        $twigParams["paymentMethods"][$payment['name']] = $payment;
      }

      /*foreach ($this->websiteRenderer->getPaymentPlugins() as $paymentPlugin) {
        $twigParams["paymentMethods"][$paymentPlugin->name] = $paymentPlugin->getPaymentMeta();
      }*/


      $twigParams["deliveryServices"] = [];
      $shipmentModel = new \ADIOS\Widgets\Shipping\Models\Shipment($this->adminPanel);
      $shipmentPriceModel = new \ADIOS\Widgets\Shipping\Models\ShipmentPrice($this->adminPanel);

      $allShipmentsBySummary = $shipmentPriceModel->getAllBySummary($twigParams["cartContents"]["summary"]);
      foreach ($allShipmentsBySummary as $shipment) {
        $twigParams["deliveryServices"][$shipment['name']] = $shipment;
        $twigParams["deliveryServices"][$shipment['name']]['PRICE'] = $shipment['shipment_price'];
      }

      /*foreach ($this->websiteRenderer->getDeliveryPlugins() as $deliveryPlugin) {
        $tmpMeta = $deliveryPlugin->getDeliveryMeta();
        if ($tmpMeta !== FALSE) {
          $twigParams["deliveryServices"][$deliveryPlugin->name] = $tmpMeta;
          $twigParams["deliveryServices"][$deliveryPlugin->name]["PRICE"] = $deliveryPlugin->calculatePriceForOrder(NULL, $twigParams["cartContents"]);
        }
      }*/

      if (isset($this->websiteRenderer->urlVariables['orderData'])) {
        $orderData = $this->websiteRenderer->urlVariables['orderData'];

        $twigParams["selectedPaymentMethod"] = $twigParams["paymentMethods"][$orderData["paymentMethod"]];
        $twigParams["selectedDeliveryService"] = $twigParams["deliveryServices"][$orderData["deliveryService"]];

        $twigParams["cartContents"]["summary"]["priceTotal"] = 
          $twigParams["cartContents"]["summary"]["priceTotal"] 
            + 
          $twigParams["deliveryServices"][$orderData["deliveryService"]]["PRICE"]
        ;

        $customerUID = $this->websiteRenderer->getCustomerUID();
        $cartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel);
        $cartContents = $cartModel->getCartContents($customerUID);

        // $deliveryPlugin = $this->websiteRenderer->getPlugin($orderData["deliveryService"]);
        $twigParams["deliveryPrice"] = $twigParams["deliveryServices"][$orderData["deliveryService"]]["PRICE"]; //$deliveryPlugin->calculatePriceForOrder($orderData, $cartContents);

        if (!empty($orderData['voucher'])) {
          $voucherModel = new \ADIOS\Widgets\Customers\Models\Voucher($this->adminPanel);
          $voucher = reset($voucherModel
            ->where('voucher', '=', $orderData['voucher'])
            ->where('valid', '=', 'Y')
            ->get()
            ->toArray()
          );

          if (isset($voucher['discount_sum'])) {
            $twigParams["voucherDiscountSum"] = $voucher['discount_sum'];
          }

          if (isset($voucher['discount_percentage'])) {
            $twigParams["voucherDiscountPercentage"] = $voucher['discount_percentage'];
          }
        }
      }

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\AdminPanel\Plugin {
  }
}