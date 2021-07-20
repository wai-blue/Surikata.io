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
            "title" => "Open",
            "onclick" => "desktop_update('Orders/Open');",
          ],
          [
            "title" => "Closed",
            "onclick" => "desktop_update('Orders/Closed');",
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


