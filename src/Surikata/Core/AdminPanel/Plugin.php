<?php

namespace Surikata\Core\AdminPanel;

class Plugin extends \ADIOS\Core\Plugin {

  var $niceName = ".";

  /*

   * Metoda parsuje URL pattern s pouzitymi premennymi v zapise {% nazovPremennej %}
   * a do premennej $siteMap vklada taky zaznam, ktory ...
   * 
   * return:
   *   array $settings = [
   *     "speed" => [
   *       "title" => "Rýchlosť prehrávania",
   *       "type" => "varchar",
   *     ]
   *   ]

  */
  public function convertUrlPatternToSiteMap(&$siteMap, $urlPattern, $variables) {
    // example of $variables argument:
    //   $variables = [
    //     "orderNumber" => '(\d+)',
    //     "checkCode" => '(.+)',
    //   ];
    
    $variablePositions = [];
    foreach ($variables as $varName => $varRegexp) {
      $variablePositions[$varName] = strpos($urlPattern, "{% {$varName} %}");
      $urlPattern = str_replace(
        "{% {$varName} %}",
        $varRegexp,
        $urlPattern
      );
    }

    asort($variablePositions);

    if (!is_array($siteMap)) {
      $siteMap = [];
    }

    $siteMap[$urlPattern] = [];

    $i = 1;
    foreach (array_keys($variablePositions) as $varName) {
      $siteMap[$urlPattern][$i++] = $varName;
    }

    return TRUE;

  }

  public function getSitemap($pluginSettings = [], $webPageUrl = "") {
    return [];
  }

  /*

   * Metoda vracia pole s definiciou nastaveni, ktore moze
   * pouzivatel nastavit pri Widgets->Website->ID->Upravit.
   * Nastavenia sa ukladaju do GTP_web_stranka.content_structure
   * 
   * return:
   *   array $settings = [
   *     "speed" => [
   *       "title" => "Rýchlosť prehrávania",
   *       "type" => "varchar",
   *     ]
   *   ]

  */
  public function getSettingsForWebsite() {
    return [];

    // PRIKLADY 
    // return [
    //   "nadpis" => [
    //     "title" => "Nadpis",
    //     "type" => "varchar",
    //   ],
    //   "obsah" => [
    //     "title" => "Obsah",
    //     "type" => "text",
    //     "interface" => "formatted_text",
    //   ],

    //   // moznost 1 pre vlastny input
    //   "menuId" => [
    //     "title" => "Ponuka menu",
    //     "input" => $this->adios->ui->Input([
    //       "type" => "int",
    //       "enum_values" => $this->adios
    //         ->getModel("Widgets/Website/Models/WebMenu")
    //         ->getEnumValues()
    //       ,
    //       "uid" => "{$inputUID}_menuId",
    //       "value" => $settings['menuId'],
    //     ]),
    //   ],

    //   // moznost 2 pre vlastny input
    //   "menuId" => [
    //     "title" => "Ponuka menu",
    //     "type" => "int",
    //     "enum_values" => $this->adios
    //       ->getModel("Widgets/Website/Models/WebMenu")
    //       ->getEnumValues()
    //     ,
    //   ],
    // ];

  }
  
}