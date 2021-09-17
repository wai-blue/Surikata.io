<?php

namespace ADIOS\Widgets;

class Shipping extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Shipping'] = [
        "fa_icon" => "fas fa-shipping-fast",
        "title" => "Shipping & Payment",
        "sub" => [
          [
            "title" => "Delivery Services",
            "onclick" => "desktop_update('Shipping/DeliveryServices');",
          ],
          [
            "title" => "Payment Services",
            "onclick" => "desktop_update('Shipping/PaymentServices');",
          ],
          [
            "title" => "Countries",
            "onclick" => "desktop_update('Shipping/Countries');",
          ],
          [
            "title" => "Shipments",
            "onclick" => "desktop_update('Shipping/Shipments');",
          ],
        ]
      ];
    }
  }
}