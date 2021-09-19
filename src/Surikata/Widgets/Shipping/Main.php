<?php

namespace ADIOS\Widgets;

class Shipping extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Shipping'] = [
        "fa_icon" => "fas fa-shipping-fast",
        "title" => "Delivery & Payment",
        "onclick" => "desktop_update('DeliveryAndPayment/DeliveryServices');",
        "sub" => [
          [
            "title" => "Delivery Services",
            "onclick" => "desktop_update('DeliveryAndPayment/DeliveryServices');",
          ],
          [
            "title" => "Payment Services",
            "onclick" => "desktop_update('DeliveryAndPayment/PaymentServices');",
          ],
          [
            "title" => "Destination countries",
            "onclick" => "desktop_update('DeliveryAndPayment/Countries');",
          ],
          [
            "title" => "Prices",
            "onclick" => "desktop_update('DeliveryAndPayment/Prices');",
          ],
        ]
      ];
    }
  }
}