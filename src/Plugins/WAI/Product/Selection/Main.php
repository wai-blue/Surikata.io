<?php

namespace Surikata\Plugins\WAI\Product {
  class Selection extends \Surikata\Core\Web\Plugin {
    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Product {
  class Selection extends \Surikata\Core\AdminPanel\Plugin {
    public function getSettingsForWebsite() {
      return [
        "layout" => [
          "title" => "Spôsob zobrazenia",
          "type" => "varchar",
          "enum_values" => [
            "grid" => "Dlaždice",
            "slideshow" => "Rolovanie",
            "list" => "Zoznam",
          ],
        ],
        "showNew" => [
          "title" => "Zobraziť novinky",
          "type" => "boolean",
        ],
        "showDiscount" => [
          "title" => "Zobraziť akciové/zľavnené produkty",
          "type" => "boolean",
        ],
        "showSale" => [
          "title" => "Zobraziť výpredajové produkty",
          "type" => "boolean",
        ],
        "showTop" => [
          "title" => "Zobraziť top produkty",
          "type" => "boolean",
        ],
        "showBestsellers" => [
          "title" => "Zobraziť najpredávanejšie produkty",
          "type" => "boolean",
        ],
        "showRecommended" => [
          "title" => "Zobraziť odporúčané produkty",
          "type" => "boolean",
        ],
        "showUsed" => [
          "title" => "Zobraziť použitý produkt",
          "type" => "boolean",
        ],
      ];
    }
    
  }
}