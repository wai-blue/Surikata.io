<?php

namespace ADIOS\Widgets;

class Prices extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_PRODUCT_MANAGER)) {
      $this->adios->config['desktop']['sidebarItems']['Prices'] = [
        "fa_icon" => "fas fa-hand-holding-usd",
        "title" => "Prices",
        "onclick" => "desktop_update('Products/Prices');",
        "sub" => [
          [
            "title" => "Margins",
            "onclick" => "window_render('Prices/Margins');",
          ],
          [
            "title" => "Discounts",
            "onclick" => "desktop_update('Products/Prices/Discounts');",
          ],
        ],
      ];
    }
  }

}