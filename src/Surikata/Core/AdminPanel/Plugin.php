<?php

namespace Surikata\Core\AdminPanel;

class Plugin extends \ADIOS\Core\Plugin {

  var $niceName = ".";
  // var $languageDictionary = [];

  public function init() {
    // $this->languageDictionary = $this->adios->loadLanguageDictionary($this);
  }

  /**
   * translate
   *
   * @internal
   * @param  mixed $string
   * @param  mixed $context
   * @param  mixed $toLanguage
   * @return void
   */
  public function translate($string) {
    return $this->adios->translate($string, $this);
  }

  /**
   * Converts $urlPattern string with variables in it to a structured
   * definition of sitemap used in getSitemap() method.
   * Used as a helper function in getSitemap().
   *
   * @return array Converted definition of sitemap (URLs) processed by the plugin.
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

  /**
   * Returns the structured definition of sitemap (compatible
   * with CASCADA's sitemap definition) processed by the plugin
   * 
   * @return array Structured sitemap definition compatible with CASCADA.
  */
  public function getSitemap($pluginSettings = [], $webPageUrl = "") {
    return [];
  }

  /**
   * Returns the definition of avaialable settings of the plugin
   * for the website content management. Each usage of the plugin
   * on each website of the e-commerce can have different settings
   * stored.
   * 
   * @return array Structured definition of available settings of the plugin for the specific web page.
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