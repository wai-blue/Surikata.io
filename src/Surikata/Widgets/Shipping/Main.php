<?php

namespace ADIOS\Widgets;

class Shipping extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Shipping'] = [
        "fa_icon" => "fas fa-shipping-fast",
        "title" => $this->translate("Delivery & Payment"),
        "onclick" => "desktop_update('DeliveryAndPayment/DeliveryServices');",
        "sub" => [
          [
            "title" => $this->translate("Delivery Services"),
            "onclick" => "desktop_update('DeliveryAndPayment/DeliveryServices');",
          ],
          [
            "title" => $this->translate("Payment Services"),
            "onclick" => "desktop_update('DeliveryAndPayment/PaymentServices');",
          ],
          [
            "title" => $this->translate("Destination countries"),
            "onclick" => "desktop_update('DeliveryAndPayment/Countries');",
          ],
          [
            "title" => $this->translate("Prices"),
            "onclick" => "desktop_update('DeliveryAndPayment/Prices');",
          ],
        ]
      ];
    }
  }
}