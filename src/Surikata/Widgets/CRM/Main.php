<?php

namespace ADIOS\Widgets;

class CRM extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $this->adios->config['desktop']['sidebarItems']['CRM'] = [
        "fa_icon" => "fas fa-award",
        "title" => "CRM",
        "sub" => [
          [
            "title" => "Contact form",
            "onclick" => "desktop_update('CRM/ContactForm');",
          ],
        ],
      ];
    }
  }
}