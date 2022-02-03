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
    $search = $this->params["search"] ?: "";
    $pluginsFound = 0;

    $plugins = [];
    foreach ($this->adios->plugins as $pluginName) {
      $pluginObject = $this->adios->getPlugin($pluginName);

      $manifest = $pluginObject->manifest();
      
      if (!empty($search) && !preg_match("/{$search}/i", $manifest['title'])) {
        continue;
      }

      $plugins[$pluginName] = $manifest['title'];
    }

    asort($plugins);

    $cardsHtml = "";
    foreach ($plugins as $pluginName => $pluginTitle) {
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
              style='color:var(--cl-main);font-size:2em;cursor:pointer;'
              onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
            ></i>
          ";
        } else {
          $logoHtml = "
            <img
              src='{$this->adios->config['url']}/adios/assets/plugins/{$pluginName}/~/{$manifest['logo']}'
              style='max-width:100%;max-height:100%;cursor:pointer'
              onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
            />
          ";
        }

        $cardsHtml .= "
          <div class='col-12'>
            <div class='card shadow mb-2'>
              <div class='card-body'>
                <div style='display:flex;align-items: center;'>
                  <div style='flex-basis:120px;height:2em;margin-right:2em;'>{$logoHtml}</div>
                  <div style='flex:1;color:var(--cl-main);'>
                    <b>".hsc($this->translate($pluginTitle))."</b>
                    <div class='small' style='color:#AAAAAA'>".hsc($pluginName)."</div>
                  </div>
                  <div style='text-align:right'>
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
            </div>
          </div>
        ";
      }
    }

    $html = $this->adios->ui->Title([
      "center" => "Plugins"
    ])->render();

    if ($pluginsFound == 0) {
      $html .= "
        <div class='row'>
          ".$this->translate("No plugins found.")."
        </div>
      ";
    } else {
      $html .= "
        <div class='row'>
          {$cardsHtml}
        </div>
      ";
    }

    return $html;
  }
}