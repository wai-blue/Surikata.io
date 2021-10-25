<?php

namespace ADIOS\Widgets;

class Settings extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ADMINISTRATOR)) {
      $this->adios->config['desktop']['sidebarItems']['Settings'] = [
        "fa_icon" => "fas fa-cog",
        "title" => $this->translate("Settings"),
        "sub" => [
          // [
          //   "title" => "Merchant profile",
          //   "onclick" => "window_render('Settings/MerchantProfile');",
          // ],
          [
            "title" => $this->translate("Units"),
            "onclick" => "desktop_update('Settings/Units');",
          ],
          [
            "title" => $this->translate("Users"),
            "onclick" => "desktop_update('Users');",
          ],
          [
            "title" => $this->translate("Miscellaneous"),
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