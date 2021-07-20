<?php

namespace ADIOS\Widgets;

class Finances extends \ADIOS\Core\Widget {
  public function init() {
    // TODO: ked budeme robit dobropisy, nastudovat toto https://www.superfaktura.sk/blog/dobropis-vzor-a-niekolko-pravidiel/
    
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Finances'] = [
        "fa_icon" => "fas fa-file-invoice-dollar",
        "title" => "Invoices",
        "onclick" => "desktop_update('Invoices');",
        "sub" => [
          [
            "title" => "Settings",
            "sub" => [
              [
                "title" => "Merchant profiles",
                "onclick" => "desktop_update('Merchants');",
              ],
              [
                "title" => "Numeric series",
                "onclick" => "desktop_update('Invoices/NumericSeries');",
              ],
            ],
          ],
        ],
      ];
    }
  }

}