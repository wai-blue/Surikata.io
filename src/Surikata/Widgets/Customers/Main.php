<?php

namespace ADIOS\Widgets;

class Customers extends \ADIOS\Core\Widget {
  public function init() {
    $this->languageDictionary["en"] = [
      "Klienti" => "Customers",
    ];

    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Customers'] = [
        "fa_icon" => "fas fa-user",
        "title" => $this->translate("Customers"),
        "onclick" => "desktop_update('Customers');",
        "sub" => [
          // [
          //   "title" => $this->translate("Filters"),
          //   "sub" => [
          //     [
          //       "title" => "Non-validated",
          //       "onclick" => "desktop_update('Customers/NonValidated');",
          //     ],
          //     [
          //       "title" => "Blocked",
          //       "onclick" => "desktop_update('Customers/Blocked');",
          //     ],
          //     [
          //       "title" => "Wholesale",
          //       "onclick" => "desktop_update('Customers/Wholeslae');",
          //     ],
          //   ],
          // ],
          [
            "title" => $this->translate("Categories"),
            "onclick" => "desktop_update('Customers/Categories');",            
          ],
          [
            "title" => "Vouchers",
            "onclick" => "desktop_update('Customers/Vouchers');",
          ],
          [
            "title" => $this->translate("Analytics"),
            "sub" => [
              [
                "title" => "Shopping Carts",
                "onclick" => "desktop_update('Customers/ShoppingCarts');",
              ],
              [
                "title" => "Searched queries",
                "onclick" => "desktop_update('Customers/SearchedQueries');",
              ],
            ],
          ],
        ],
      ];
    }
  }
}