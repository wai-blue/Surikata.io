<?php

namespace ADIOS\Widgets;

class Finances extends \ADIOS\Core\Widget {
  public function init() {
    // TODO: ked budeme robit dobropisy, nastudovat toto https://www.superfaktura.sk/blog/dobropis-vzor-a-niekolko-pravidiel/

    $this->languageDictionary["sk"] = [
      "Invoices" => "Faktúry",
      "Merchant profiles" => "Profily obchodníkov",
      "Numeric series" => "Číselná séria",
      "Settings" => "Nastavenia"
    ];
    
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['Finances'] = [
        "fa_icon" => "fas fa-file-invoice-dollar",
        "title" => $this->translate("Invoices"),
        "onclick" => "desktop_update('Invoices');",
        "sub" => [
          [
            "title" => $this->translate("Settings"),
            "sub" => [
              [
                "title" =>  $this->translate("Merchant profiles"),
                "onclick" => "desktop_update('Merchants');",
              ],
              [
                "title" =>  $this->translate("Numeric series"),
                "onclick" => "desktop_update('Invoices/NumericSeries');",
              ],
            ],
          ],
        ],
      ];
    }
  }

}