<?php

namespace Surikata\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\Web\Plugin {
    var $cartContents = NULL;
    var $shipping = NULL;

    public function getTotalPriceWithDelivery($deliveryServicePrice) {
      return
        $this->cartContents["summary"]["priceTotal"] 
          + 
        $deliveryServicePrice
      ;
    }

    public function getPaymentMethods($selectedDeliveryService) {
      $paymentMethods = [];
      foreach ($this->shipping as $shipment) {
        if ($shipment['delivery']['id'] == $selectedDeliveryService['id']) {
          $paymentMethods[$shipment['payment']['id']] = $shipment['payment'];
        }
      }

      return $paymentMethods;
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $userProfileController = 
        new \Surikata\Core\Web\Controllers\UserProfile(
          $this->websiteRenderer
        )
      ;

      $shipmentModel = 
        new \ADIOS\Widgets\Shipping\Models\Shipment(
          $this->adminPanel
        )
      ;

      $userProfileController->reloadUserProfile();
      $twigParams['userLogged'] = $this->websiteRenderer->userLogged;

      $this->cartContents = 
        (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))
        ->getCartContents()
      ;

      $deliveryServices = [];

      if ($this->shipping === NULL) {
        $this->shipping = 
          $shipmentModel->getByCartSummary(
            $this->cartContents["summary"]
          )
        ;
      }

      foreach ($this->shipping as $shipment) {
        $deliveryServices[$shipment['delivery']['id']] = $shipment['delivery'];
      }

      if (isset($this->websiteRenderer->urlVariables['orderData'])) {
        $orderData = $this->websiteRenderer->urlVariables['orderData'];

        $selectedDeliveryService = $deliveryServices[$orderData["deliveryService"]];
        $paymentMethods = $this->getPaymentMethods($selectedDeliveryService);
        $selectedPaymentMethod = $paymentMethods[$orderData["paymentMethod"]];

        if ($selectedPaymentMethod == NULL) {
          $selectedPaymentMethod = reset($paymentMethods);
        }

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
      } else {
        $selectedDeliveryService = reset($this->shipping)['delivery'];
        $paymentMethods = $this->getPaymentMethods($selectedDeliveryService);
        $selectedPaymentMethod = reset($paymentMethods);
      }

      foreach ($this->shipping as $shipment) {
        if (
          $shipment['delivery']['id'] == $selectedDeliveryService['id']
          && $shipment['payment']['id'] == $selectedPaymentMethod['id']
        ) {
          $currentShipment = $shipment;
        }
      }

      $twigParams['totalPriceWithDelivery'] = 
        $this->getTotalPriceWithDelivery(
          $currentShipment['price']['shipment_price']
        )
      ;
      $twigParams['deliveryPrice'] = $currentShipment['price']['shipment_price'];
      $twigParams['cartContents'] = $this->cartContents;
      $twigParams["deliveryServices"] = $deliveryServices;
      $twigParams['paymentMethods'] = $paymentMethods;
      $twigParams["selectedDeliveryService"] = $selectedDeliveryService;
      $twigParams["selectedPaymentMethod"] = $selectedPaymentMethod;

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\AdminPanel\Plugin {
  }
}