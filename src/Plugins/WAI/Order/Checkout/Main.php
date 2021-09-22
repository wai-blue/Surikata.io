<?php

namespace Surikata\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\Web\Plugin {
    var $cartContents = NULL;
    var $shipping = NULL;
    var $selectedDestinationCountryId = NULL;

    public function getPaymentMethods($selectedDeliveryService) {
      $paymentMethods = [];
      foreach ($this->shipping as $shipment) {
        if (
          $shipment['id_delivery_service'] == $selectedDeliveryService['id']
          && $shipment['id_destination_country'] == $this->selectedDestinationCountryId
          ) {
          $paymentMethods[$shipment['id_payment_service']] = $shipment['payment'];
          $paymentMethods[$shipment['id_payment_service']]['price'] = 
            $shipment['price']['payment_fee']
          ;
        }
      }

      return $paymentMethods;
    }

    public function getDeliveryServices() {
      $deliveryServices = [];
      foreach ($this->shipping as $index => $shipment) {
        $this->shipping[$index]['price'] = reset($shipment['price']);
        if (
          !array_key_exists($shipment['id_delivery_service'], $deliveryServices)
          && $shipment['id_destination_country'] == $this->selectedDestinationCountryId
        ) {
          $deliveryServices[$shipment['id_delivery_service']] = $shipment['delivery'];
          $deliveryServices[$shipment['id_delivery_service']]['price'] = 
            reset($shipment['price'])['delivery_fee']
          ;
        }
      }

      return $deliveryServices;
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

      $destinationCountryModel = 
        new \ADIOS\Widgets\Shipping\Models\DestinationCountry(
          $this->adminPanel
        )
      ;

      $userProfileController->reloadUserProfile();
      $twigParams['userLogged'] = $this->websiteRenderer->userLogged;

      $this->cartContents = 
        (new \Surikata\Plugins\WAI\Customer\Cart($this->websiteRenderer))
        ->getCartContents()
      ;

      if ($this->shipping === NULL) {
        $this->shipping = 
          $shipmentModel->getByCartSummary(
            $this->cartContents["summary"]
          )
        ;
      }

      $destinationCountries = [];
      $destinationCountries = $destinationCountryModel->getAll();

      if (isset($this->websiteRenderer->urlVariables['orderData'])) {
        $orderData = $this->websiteRenderer->urlVariables['orderData'];
        $this->selectedDestinationCountryId = $orderData["id_destination_country"];

        $deliveryServices = $this->getDeliveryServices();
        $selectedDeliveryService = 
          $deliveryServices[$orderData["id_delivery_service"]] 
          ?? reset($deliveryServices)
        ;

        $paymentMethods = $this->getPaymentMethods($selectedDeliveryService);
        $selectedPaymentMethod = 
          $paymentMethods[$orderData["id_payment_service"]] 
          ?? reset($paymentMethods)
        ;

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
        $this->selectedDestinationCountryId = reset($destinationCountries)['id'];
        $deliveryServices = $this->getDeliveryServices();
        $selectedDeliveryService = reset($deliveryServices);
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

      $twigParams['deliveryPrice'] = (
        floatval($currentShipment['price']['delivery_fee']) 
          + 
        floatval($currentShipment['price']['payment_fee'])
      );

      $twigParams['totalPriceWithDelivery'] = 
        floatval($this->cartContents["summary"]["priceTotal"]) 
          + 
        floatval($twigParams['deliveryPrice'])
      ;

      $twigParams['cartContents'] = $this->cartContents;

      $twigParams["deliveryServices"] = $deliveryServices;
      $twigParams['paymentMethods'] = $paymentMethods;
      $twigParams["destinationCountries"] = $destinationCountries;

      $twigParams["selectedDeliveryService"] = $selectedDeliveryService;
      $twigParams["selectedPaymentMethod"] = $selectedPaymentMethod;
      $twigParams["selectedDestinationCountryId"] = $this->selectedDestinationCountryId;

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Order {
  class Checkout extends \Surikata\Core\AdminPanel\Plugin {
  }
}