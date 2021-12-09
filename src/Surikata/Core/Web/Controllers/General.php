<?php

namespace Surikata\Core\Web\Controllers;

class General extends \Surikata\Core\Web\Controller {

  public function renderPlugin($pluginName, $pluginSettings, $panelName = "") {
    $plugin = $this->websiteRenderer->getPlugin($pluginName);

    $renderParams = array_merge(
      [
        "panel" => $panelName,
        "pluginName" => $pluginName,
      ],
      $pluginSettings ?? [],
      $plugin->getTwigParams($pluginSettings ?? []),
      $this->websiteRenderer->twigParams,
      $this->websiteRenderer->getGlobalTwigParams()
    );

    $renderParams = $this->adminPanel->dispatchEventToPlugins("onGeneralControllerAfterRenderPlugin", [
      "controller" => $this,
      "pluginName" => $pluginName,
      "pluginSettings" => $pluginSettings,
      "panelName" => $panelName,
      "renderParams" => $renderParams,
    ])["renderParams"];

    if (is_object($plugin)) {
      $this->websiteRenderer->currentRenderedPlugin = $plugin;

      if (($this->websiteRenderer->urlVariables["__output"] ?? "") == "json") {
        $html = json_encode($plugin->renderJSON($renderParams));
      } else {
        $templateFile = "{$this->websiteRenderer->themeDir}/Templates/Plugins/{$pluginName}.twig";

        if (is_file($templateFile)) {
          $renderParams["system"]["availableVariables"] = array_keys($renderParams);

          $this->websiteRenderer->currentRenderedPlugin->twigRenderParams = $renderParams;

          $html = $this->websiteRenderer->twig
            ->render("Templates/Plugins/{$pluginName}.twig", $renderParams)
          ;
        } else if (($tmpRawHtml = $plugin->render($renderParams)) !== NULL) {
          $html = $tmpRawHtml;
        } else {
          $html = "Template for plugin {$pluginName} not found.";
        }
      }

      $this->websiteRenderer->currentRenderedPlugin = NULL;
    }

    return $html;
  }

  public function renderPlugins($contentStructure = []) {

    // rozparsujem nastavenia layoutu
    if (is_array($contentStructure)) {

      // inicializacia TWIG parametrov; urcite budem potrebovat
      // {{ panels.NAZOV_PANELU.NEJAKE_NASTAVENIE }}

      $twigParams = [
        "panels" => [],
      ];

      // vygenerujem HTML kazdeho panelu v layoute, ak ma panel
      // nastaveny plugin, ktory sa ma v nom zobrazit.
      // Pri renderovani pluginu sa pouziva kombinacia parametrov:
      //   - {{ panel }} = nazov panelu
      //       toto umozni pri renderovani pluginu vziat do uvahy, ci
      //       je potrebne HTML napr. pre sidebar alebo main_content
      //   - nastavenia od pouzivatela ($panelSettings['settings'])
      //   - parametre vygenerovane metodou $plugin->getTwigParams()
      //   - uz vygenerovane TwigParams v ramci CASCADA Controllers,
      //       napr. rootUrl, pageUrl a pod. ($this->twigParams)

      foreach ($contentStructure as $panelName => $panelSettings) {
        if (!empty($panelSettings["plugin"])) {
          $tmpHtml = $this->renderPlugin(
            $panelSettings["plugin"],
            $panelSettings['settings'],
            $panelName
          );

          $twigParams["plugins"][$panelSettings["plugin"]]["html"] = $tmpHtml;
          $twigParams["panels"][$panelName]["html"] = $tmpHtml;
        }
      }

      // pripravene TwigParams podsuniem do CASCADy na renderovanie
      // layout .twig sablony

      $this->websiteRenderer->setTwigParams($twigParams);
    }
  }

  public function preRender() {
    // v tomto momente uz CASCADA router zistil, aka webstranka sa ma zobrazit,
    // cize viem si vytiahnut nastavenia stranky (z GTP_web_stranky)

    $this->websiteRenderer->idWebPage = $this->websiteRenderer->urlVariables["idWebPage"] ?? 0;
    $this->websiteRenderer->currentPage = $this->websiteRenderer->pages[$this->websiteRenderer->idWebPage] ?? NULL;

    if ($this->websiteRenderer->currentPage === NULL) {
      header("Location: {$this->websiteRenderer->rewriteBase}");
      exit();
    }

    $this->websiteRenderer->onGeneralControllerAfterRouting() ;

    $this->adminPanel->dispatchEventToPlugins("onGeneralControllerPreRender", [
      "controller" => $this,
    ]);

    $this->websiteRenderer->setTwigParams([
      "customerUID" => $this->websiteRenderer->getCustomerUID(),
      "currentYear" => date("Y"),
      "today" => date("Y-m-d"),
      "settings" => $this->adminPanel->webSettings,
      "domain" => $this->websiteRenderer->config['domainToRender'],
      "languageIndex" => $this->websiteRenderer->domain['languageIndex'],
      "urlVariables" => $this->websiteRenderer->urlVariables,
      "uploadedFileUrl" => $this->adminPanel->config['files_url'],
      "header" => [
        "metaKeywords" => $this->websiteRenderer->currentPage["seo_keywords"] ?? "",
        "metaDescription" => $this->websiteRenderer->currentPage["seo_description"] ?? "",
        "pageTitle" => $this->websiteRenderer->currentPage["seo_title"] ?? "",
      ],
      "locale" => $this->adminPanel->locale->getAll(),
    ]);

    $adminPanelConfig = $this->websiteRenderer->adminPanel->config;
    $maintenanceSettings = $adminPanelConfig["settings"]["web"]["maintenance"] ?? [];
    $maintenanceModeActivated = (bool) ($maintenanceSettings["activated"] ?? FALSE);

    if ($maintenanceModeActivated) {
      $this->websiteRenderer->outputHtml =
        $this->websiteRenderer->twig->render(
          "{$this->websiteRenderer->twigTemplatesSubDir}/Maintenance.twig",
          array_merge(
            $this->websiteRenderer->twigParams,
            [
              "additionalInfo" => $maintenanceSettings["additionalInfo"]
            ]
          )
        );
      $this->websiteRenderer->cancelRendering();
    }

    if (empty($_GET['__renderOnlyPlugin'])) {
      if (is_array($this->websiteRenderer->contentStructure)) {
        $contentStructure = $this->websiteRenderer->contentStructure;
      } else {
        $tmp = @json_decode(
          ($this->websiteRenderer->currentPage['content_structure'] ?? ""),
          TRUE
        );
        $contentStructure = $tmp["panels"] ?? [];
      }

      $this->renderPlugins($contentStructure);
    } else {
      $pluginName = $_GET['__renderOnlyPlugin'];
      $pluginSettings = $this->websiteRenderer->getCurrentPagePluginSettings($pluginName);

      $this->websiteRenderer->outputHtml = $this->renderPlugin($pluginName, $pluginSettings);
      $this->websiteRenderer->cancelRendering();
    }

  }

}
