<?php

namespace Surikata\Core\Web;

class Plugin {
  var $websiteRenderer;
  var $adminPanel;
  var $name = "";
  var $twigRenderParams = [];

  public function __construct($websiteRenderer) {
    $this->name = str_replace("\\", "/", str_replace("Surikata\\Plugins\\", "", get_class($this)));
    $this->websiteRenderer = &$websiteRenderer;
    $this->adminPanel = $this->websiteRenderer->adminPanel;
    $this->twigRenderParams = [];
  }

  /**
   * Replaces placeholders for variables inside the URL string
   * with values of the variables.
   *
   * @param string $url The URL containing variable placeholders in {% variableName %} format.
   * @param array $urlVariables Variable values to be replaced.
   *
   * @return string URL string with replaced variable values.
   */
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
   * @param string optional $domain Domain for which the URL should be generated.
   * 
   * @return string URL of the website without {{ rootUrl }}
   */
  public function getWebPageUrlFormatted($urlVariables, $pluginSettings = [], $domain = "") {
    return NULL;
  }

  /**
   * Returns the URL of the website where the plugin is used. Uses Widgets/Website/Models/WebPage model.
   * 
   * @param array $urlVariables Specific variables for generating the URL. In most cases the values of variables insied the URL - e.g. ID of the product.
   * @param string $domain Name of the domain for which the URL is to be generated. Used when calling this method from the administration panel, e.g. when generating URL for a specific order.
   * 
   * @return string URL of the website without {{ rootUrl }}
   */
  public function getWebPageUrl($urlVariables = [], $domain = "") {
    $url = NULL;

    $publishedPages = $this->websiteRenderer->loadPublishedPages($domain);

    foreach ($publishedPages as $webPage) {
      $contentStructure = @json_decode($webPage['content_structure'], TRUE);

      if ($url === NULL) {
        foreach ($contentStructure['panels'] as $panelName => $panelSettings) {
          if (($panelSettings["plugin"] ?? "") == $this->name) {
            $url = $this->getWebPageUrlFormatted($urlVariables, $panelSettings["settings"], $domain);

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

  /**
   * Returns the ID of the website where the plugin is used. Uses Widgets/Website/Models/WebPage model.
   * 
   * @param string $domain Name of the domain for which the URL is to be generated. Used when calling this method from the administration panel, e.g. when generating URL for a specific order.
   * @param array $pluginSettings OPTIONAL. If provided, settings of the plugin must match this argument.
   * 
   * @return string ID of the website
   */
  public function getWebPageId($domain = "", $pluginSettings = []) {
    $idWebPage = NULL;

    $publishedPages = $this->websiteRenderer->loadPublishedPages($domain);

    foreach ($publishedPages as $webPage) {
      $contentStructure = @json_decode($webPage['content_structure'], TRUE);

      if ($idWebPage === NULL) {
        foreach ($contentStructure['panels'] as $panelName => $panelSettings) {
          if (($panelSettings["plugin"] ?? "") == $this->name) {
            $match = TRUE;

            // TODO: preverit aj voci $domain

            // TODO: ak $pluginSettings je neprazdne, tak preverit, ci
            // $pluginSettings nie je v protiklade s $panelSettings["settings"]

            if ($match) {
              $idWebPage = $webPage['id'];
              break;
            }
          }
        }
      }
    }

    return $idWebPage;
  }
  /**
   * Returns the key - value associative array of variables and
   * their values used in TWIG template.
   * 
   * @param array $pluginSettings Settings of the plugin to be rendered. Can be different for multiple usage of the plugin accros the website.
   * 
   * @return array Key-Value pair of variable names and their values.
   */
  public function getTwigParams($pluginSettings) {
    // UPOZORNENIE: TATO METODA BY NEMALA PRACOVAT S DATABAZOU
    // Dovod: ak je 1 plugin pouzity vo viac paneloch, tato metoda
    // sa spusta pri kazdom paneli. Lepsie riesenie je potrebne
    // data z DB nacitat v __construct();
    
    return $pluginSettings;
  }

  /**
   * GET globalTwigParams
   * @return array
   */
  public function getGlobalTwigParams() {
    return [];
  }

  /**
   * Renders content of the plugin directly as a HTML string.
   * Disables TWIG rendering.
   *
   * @return null|string Default implementation returns NULL. Custom implementation should return HTML string.
   */
  public function render() {
    return NULL;
  }

}