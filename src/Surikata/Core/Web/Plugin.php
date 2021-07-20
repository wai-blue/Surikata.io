<?php

namespace Surikata\Core\Web;

class Plugin {
  var $websiteRenderer;
  var $adminPanel;
  var $name = "";

  public function __construct($websiteRenderer) {
    $this->name = str_replace("\\", "/", str_replace("Surikata\\Plugins\\", "", get_class($this)));
    $this->websiteRenderer = &$websiteRenderer;
    $this->adminPanel = $this->websiteRenderer->adminPanel;
  }

  public function replaceUrlVariables($url, $urlVariables) {

    foreach ($urlVariables as $varName => $varValue) {
      $url = str_replace("{% {$varName} %}", $varValue, $url);
    }

    $url = preg_replace('/{% .*? %}/', '', $url);

    return $url;

  }

  /**
   * Returns the URL of the website where the plugin is used.
   * Returned URL can be modified using $pluginParams or $pluginSettings.
   * 
   * @param array $urlVariables Specific variables for generating the URL. In most cases the values of variables insied the URL - e.g. ID of the product.
   * @param array optional $pluginSettings Settings of the plugin from the administration panel. E.g. the URL pattern defined by the administrator.
   * 
   * @return string URL of the website without {{ rootUrl }}
   */
  public function getWebPageUrlFormatted($urlVariables, $pluginSettings = []) {
    return NULL;
  }

  /**
   * Returns the URL of the website where the plugin is used. Uses Widgets/Website/Models/WebPage model.
   * 
   * @param array $urlVariables Specific variables for generating the URL. In most cases the values of variables insied the URL - e.g. ID of the product.
   * 
   * @return string URL of the website without {{ rootUrl }}
   */
  public function getWebPageUrl($urlVariables = []) {
    $url = NULL;

    foreach ($this->websiteRenderer->pages as $webPage) {
      $contentStructure = @json_decode($webPage['content_structure'], TRUE);

      if ($url === NULL) {
        foreach ($contentStructure['panels'] as $panelName => $panelSettings) {
          if (($panelSettings["plugin"] ?? "") == $this->name) {
            $url = $this->getWebPageUrlFormatted($urlVariables, $panelSettings["settings"]);

            if ($url === NULL) {
              // Tu je este moznost, ze plugin sice nema overridnutu
              // getWebPageUrlFormatted, ale je pouzity v ramci sitemapy
              // viackrat, pricom kazde pouzitie ma svoje nastavenia.
              // Preto, ak urlVariables je neprazdne, sa pokusam najst
              // taku webstranku, v ktorej je plugin pouzity s nastaveniami
              // zhodnymi s urlVariables.

              if (empty($urlVariables)) {
                $url = $webPage['url'];
              } else {

                $match = TRUE;
                $pluginSettings = $panelSettings["settings"] ?? [];

                foreach ($urlVariables as $varName => $varValue) {
                  if (!isset($pluginSettings[$varName])) $match = FALSE;
                  else if ($pluginSettings[$varName] != $varValue) $match = FALSE;
                }

                if ($match) $url = $webPage['url'];
              }
            }

            if ($url !== NULL) break;
          }
        }
      }
    }

    return $url;
  }

  public function getTwigParams($pluginSettings) {
    // UPOZORNENIE: TATO METODA BY NEMALA PRACOVAT S DATABAZOU
    // Dovod: ak je 1 plugin pouzity vo viac paneloch, tato metoda
    // sa spusta pri kazdom paneli. Lepsie riesenie je potrebne
    // data z DB nacitat v __construct();
    
    return $pluginSettings;
  }

  /**
   * Renders content of the plugin without using CASCADA and Twig.
   * @return null|string Default implementation returns NULL. Custom implementation should return HTML string.
   */
  public function render() {

  }

}