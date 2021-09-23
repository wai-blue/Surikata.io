<?php

namespace ADIOS\Widgets;

class Settings extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ADMINISTRATOR)) {
      $this->adios->config['desktop']['sidebarItems']['Settings'] = [
        "fa_icon" => "fas fa-cog",
        "title" => "Settings",
        "sub" => [
          // [
          //   "title" => "Merchant profile",
          //   "onclick" => "window_render('Settings/MerchantProfile');",
          // ],
          [
            "title" => "Units",
            "onclick" => "desktop_update('Settings/Units');",
          ],
          [
            "title" => "Users",
            "onclick" => "desktop_update('Users');",
          ],
          [
            "title" => "Miscellaneous",
            "onclick" => "window_render('Settings/Miscellaneous');",
          ],
          //[
          //  "title" => "Imports",
          //  "onclick" => "desktop_update('Settings/Imports');",
          //],
        ],
      ];
    }
  }

}