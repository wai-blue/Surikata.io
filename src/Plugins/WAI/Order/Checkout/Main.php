<?php

namespace Surikata\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\Web\Plugin {
    var $cartContents = NULL;

    /**
     * Get products price with delivery price
     * @return void
     */
    public function getPriceTotal($deliveryServicePrice) {
      $this->cartContents["summary"]["priceTotal"] = 
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

      $allShipmentsByCartSummary = 
        $shipmentPriceModel->getAllBySummary(
          $this->cartContents["summary"]
        )
      ;

      foreach ($allShipmentsByCartSummary as $shipment) {
        $deliveryServices[$shipment['id']] = $shipment;
        $deliveryServices[$shipment['id']]['PRICE'] = $shipment['shipment_price'];
      }

      $selectedDeliveryService = reset($deliveryServices);

      $shipmentPayments = 
        $shipmentModel->getByIdDeliveryService(
          $selectedDeliveryService["id"]
        )
      ;

      $paymentMethods = [];
      foreach ($shipmentPayments as $shipmentPayment) {
        $paymentMethods[$shipmentPayment['payment']['id']] = $shipmentPayment['payment'];
      }

      $selectedPaymentMethod = (reset($shipmentPayments))['payment'];

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
            $selectedPaymentMethod["id"]
          )
        ; 

        $shipmentPrice = $shipmentPriceModel->getById($shipment['id']);

        $this->getPriceTotal(
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

        $this->getPriceTotal(
          $deliveryServices[$selectedDeliveryService["id"]]["PRICE"]
        );

        $twigParams['deliveryPrice'] = $deliveryServices[$selectedDeliveryService["id"]]["PRICE"];

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