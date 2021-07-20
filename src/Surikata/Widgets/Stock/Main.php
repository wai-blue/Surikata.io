<?php

namespace ADIOS\Widgets;

class Stock extends \ADIOS\Core\Widget {
  public function init() {
    if (
      $this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_PRODUCT_MANAGER)
      || $this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)
    ) {

      // $this->adios->config['desktop']['sidebarItems']['Stock'] = [
      //   "fa_icon" => "fas fa-warehouse",
      //   "title" => "Stock",
      //   "sub" => [
      //     [
      //       "title" => "Stocks",
      //       "onclick" => "desktop_update('');",
      //     ],
      //     [
      //       "title" => "Príjem",
      //       "onclick" => "desktop_update('');",
      //     ],
      //     [
      //       "title" => "Výdaj",
      //       "onclick" => "desktop_update('');",
      //     ],
      //     [
      //       "title" => "Rezervácie",
      //       "onclick" => "desktop_update('');",
      //     ],
      //     [
      //       "title" => "Pohyby",
      //       "onclick" => "desktop_update('');",
      //     ],
      //     [
      //       "title" => "Inventúra",
      //       "onclick" => "desktop_update('');",
      //     ],
      //   ],
      // ];

    }
  }
}