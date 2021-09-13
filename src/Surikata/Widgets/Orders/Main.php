<?php

namespace ADIOS\Widgets;

class Orders extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Orders'] = [
        "fa_icon" => "fas fa-shopping-basket",
        "title" => "Orders",
        "onclick" => "desktop_update('Orders');",
        "sub" => [
          [
            "title" => "New",
            "onclick" => "desktop_update('Orders/New');",
          ],
          [
            "title" => "Invoice issued",
            "onclick" => "desktop_update('Orders/InvoiceIssued');",
          ],
          [
            "title" => "Paid and not shipped",
            "onclick" => "desktop_update('Orders/Paid');",
          ],
          [
            "title" => "Shipped",
            "onclick" => "desktop_update('Orders/Shipped');",
          ],
          [
            "title" => "Canceled",
            "onclick" => "desktop_update('Orders/Canceled');",
          ],
          //[
          //  "title" => "Claims",
          //  "onclick" => "desktop_update('Claims');",
          //],
        ],
      ];
    }
  }

}


