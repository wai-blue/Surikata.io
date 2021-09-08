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

      $shipmentPriceModel = 
        new \ADIOS\Widgets\Shipping\Models\ShipmentPrice(
          $this->adminPanel
        )
      ;

      $userProfileController->reloadUserProfile();
      $twigParams['userLogged'] = $this->websiteRenderer->userLogged;

      $this->cartContents = 
        (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))
        ->getCartContents()
      ;

      /*foreach ($this->websiteRenderer->getPaymentPlugins() as $paymentPlugin) {
        $twigParams["paymentMethods"][$paymentPlugin->name] = $paymentPlugin->getPaymentMeta();
      }*/

      $deliveryServices = [];

      if ($this->shipping === NULL) {
        $this->shipping = 
          $shipmentPriceModel->getAllBySummary(
            $this->cartContents["summary"]
          )
        ;
      }

      foreach ($this->shipping as $shipment) {
        $deliveryServices[$shipment['id']] = $shipment;
        $deliveryServices[$shipment['id']]['PRICE'] = $shipment['shipment_price'];
      }

      $selectedDeliveryService = reset($this->shipping);

      /*$shipmentPayments = 
        $shipmentModel->getByIdDeliveryService(
          $selectedDeliveryService["id"]
        )
      ;*/

      $paymentMethods = [];
      foreach ($this->shipping as $shipment) {
        if ($shipment['id'] == $selectedDeliveryService['id']) {
          $paymentMethods[$shipment['id_payment_service']] = $shipment;
        }
      }

      $selectedPaymentMethod = reset($paymentMethods);

      /*foreach ($this->websiteRenderer->getDeliveryPlugins() as $deliveryPlugin) {
        $tmpMeta = $deliveryPlugin->getDeliveryMeta();
        if ($tmpMeta !== FALSE) {
          $deliveryServices[$deliveryPlugin->name] = $tmpMeta;
          $deliveryServices[$deliveryPlugin->name]["PRICE"] = $deliveryPlugin->calculatePriceForOrder(NULL, $this->cartContents);
        }
      }*/

      if (isset($this->websiteRenderer->urlVariables['orderData'])) {
        $orderData = $this->websiteRenderer->urlVariables['orderData'];

        $selectedPaymentMethod = $paymentMethods[$orderData["paymentMethod"]];
        $selectedDeliveryService = $deliveryServices[$orderData["deliveryService"]];

        $shipment = 
          $shipmentModel->getShipment(
            $selectedDeliveryService["id"],
            $selectedPaymentMethod["id_payment_service"]
          )
        ; 

        $shipmentPrice = $shipmentPriceModel->getById($shipment['id']);

        $twigParams['totalPriceWithDelivery'] = $this->getTotalPriceWithDelivery(
          $shipmentPrice['shipment_price']
        );

        $twigParams['deliveryPrice'] = $shipmentPrice['shipment_price'];

        $shipmentPayments = 
          $shipmentModel->getByIdDeliveryService(
            $selectedDeliveryService["id"]
          )
        ;

        $paymentMethods = [];
        foreach ($shipmentPayments as $shipmentPayment) {
          $paymentMethods[$shipmentPayment['payment']['name']] = $shipmentPayment['payment'];
        }

        //$customerUID = $this->websiteRenderer->getCustomerUID();
        //$cartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel);
        //$this->cartContents = $cartModel->getCartContents($customerUID);

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

        $twigParams['totalPriceWithDelivery'] = 
          $this->getTotalPriceWithDelivery(
            $selectedDeliveryService["shipment_price"]
          )
        ;

        $twigParams['deliveryPrice'] = $selectedDeliveryService["shipment_price"];

      }

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