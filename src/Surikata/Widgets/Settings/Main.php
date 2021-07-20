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
            "title" => "Delivery defaults",
            "onclick" => "window_render('Settings/DeliveryDefaults');",
          ],
          [
            "title" => "Units",
            "onclick" => "window_render('Settings/Units');",
          ],
          [
            "title" => "Translations",
            "onclick" => "window_render('Settings/Translations');",
          ],
          [
            "title" => "Users",
            "onclick" => "desktop_update('Pouzivatelia');",
          ],
          [
            "title" => "Imports",
            "onclick" => "desktop_update('Settings/Imports');",
          ],
          [
            "title" => "Maintenance",
            "onclick" => "window_render('Settings/Maintenance');",
          ],
        ],
      ];
    }
  }

}