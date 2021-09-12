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

      $selectedDeliveryService = reset($this->shipping)['delivery'];

      $paymentMethods = [];
      foreach ($this->shipping as $shipment) {
        if ($shipment['delivery']['id'] == $selectedDeliveryService['id']) {
          $paymentMethods[$shipment['payment']['id']] = $shipment['payment'];
        }
      }

      $selectedPaymentMethod = reset($paymentMethods);

      if (isset($this->websiteRenderer->urlVariables['orderData'])) {
        $orderData = $this->websiteRenderer->urlVariables['orderData'];

        $selectedPaymentMethod = $paymentMethods[$orderData["paymentMethod"]];
        $selectedDeliveryService = $deliveryServices[$orderData["deliveryService"]];

        $shipmentPayments = 
          $shipmentModel->getByIdDeliveryService(
            $selectedDeliveryService["id"]
          )
        ;

        $paymentMethods = [];
        foreach ($shipmentPayments as $shipmentPayment) {
          $paymentMethods[$shipmentPayment['payment']['name']] = 
            $shipmentPayment['payment']
          ;
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