<?php

namespace ADIOS\Actions\Plugins;

class Overview extends \ADIOS\Core\Widget\Action {
  
  public function onAfterDesktopPreRender($params) {
    $params["searchButton"] = [
      "display" => TRUE,
      "placeholder" => $this->translate("Search plugins..."),
    ];

    return $params;
  }

  public function render() {
    $plugins = $this->adios->plugins;
    $search = $this->params["search"] ?: "";
    $pluginsFound = 0;

    $pluginsWithLogo = [];
    $pluginsWithoutLogo = [];
    foreach ($plugins as $pluginName) {
      $pluginObject = $this->adios->getPlugin($pluginName);

      $manifest = $pluginObject->manifest();
      
      if (!empty($search) && !preg_match("/{$search}/i", $manifest['title'])) {
        continue;
      }

      if (empty($manifest['logo'])) {
        $pluginsWithoutLogo[] = $pluginName;
      } else {
        $pluginsWithLogo[] = $pluginName;
      }
    }

    $cardsHtml = "";
    foreach ([...$pluginsWithLogo, ...$pluginsWithoutLogo] as $pluginName) {
      $pluginsFound++;

      $mainActionExists = $this->adios->actionExists("Plugins/{$pluginName}/Main");
      $settingsActionExists = $this->adios->actionExists("Plugins/{$pluginName}/Settings");

      if ($mainActionExists || $settingsActionExists) {
        $pluginObject = $this->adios->getPlugin($pluginName);
        $manifest = $pluginObject->manifest();

        if (empty($manifest['logo'])) {
          $logoHtml = "
            <i
              class='{$manifest['faIcon']}'
              style='color:var(--cl-main);font-size:60px;cursor:pointer;margin-top:30px;'
              onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
            ></i>
          ";
        } else {
          $logoHtml = "
            <img
              src='{$this->adios->config['url']}/adios/assets/plugins/{$pluginName}/~/{$manifest['logo']}'
              style='max-width:100%;max-height:120px;cursor:pointer'
              onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
            />
          ";
        }

        $cardsHtml .= "
          <div class='col-lg-3 col-md-6 col-sm-12'>
            <div class='card shadow mb-2'>
              <div class='card-header py-3'>
                <h6 class='m-0 font-weight-bold text-primary'>".$this->translate(hsc($manifest['title']))."</h6>
              </div>
              <div class='card-body text-center'>
                <div style='height:130px'>
                  {$logoHtml}
                </div>
                ".($mainActionExists ? "
                  <a
                    href='javascript:void(0)'
                    class='btn btn-light'
                    onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
                  >".$this->translate("Manage")."</a>
                " : "")."
                ".($settingsActionExists ? "
                  <a
                    href='javascript:void(0)'
                    class='btn btn-light'
                    onclick='window_render(\"Plugins/{$pluginName}/Settings\");'
                  >".$this->translate("Settings")."</a>
                " : "")."
              </div>
            </div>
          </div>
        ";
      }
    }

    if ($pluginsFound == 0) {
      $html = "
        <div class='row'>
          ".$this->translate("No plugins found.")."
        </div>
      ";
    } else {
      $html = "
        <div class='row'>
          {$cardsHtml}
        </div>
      ";
    }

    return $html;
  }
}