<?php

namespace ADIOS\Actions\Plugins;

class Overview extends \ADIOS\Core\Widget\Action {
  
  // public function onAfterDesktopPreRender($params) {
  //   $params["searchButton"] = [
  //     "display" => TRUE,
  //     "placeholder" => $this->translate("Search plugins..."),
  //   ];

  //   return $params;
  // }

  public function render() {
    $search = $this->params["search"] ?: "";
    $pluginsFound = 0;

    // zistim si info o pluginoch
    $pluginsWithIcon = [];
    $pluginsWithoutIcon = [];
    foreach ($this->adios->plugins as $pluginName) {
      $pluginObject = $this->adios->getPlugin($pluginName);

      $manifest = $pluginObject->manifest();
      
      if (!empty($search) && !preg_match("/{$search}/i", $manifest['title'])) {
        continue;
      }

      if (empty($manifest['faIcon']) && empty($manifest['logo'])) {
        $pluginsWithoutIcon[$pluginName] = $manifest['title'];
      } else {
        $pluginsWithIcon[$pluginName] = $manifest['title'];
      }
    }

    // zotriedim ich tak, aby tie s ikonkou alebo obrazkom boli prve
    asort($pluginsWithIcon);
    asort($pluginsWithoutIcon);
    $plugins = $pluginsWithIcon + $pluginsWithoutIcon;

    // zobrazim ich
    $cardsHtml = "";
    foreach ($plugins as $pluginName => $pluginTitle) {
      $pluginsFound++;

      $mainActionExists = $this->adios->actionExists("Plugins/{$pluginName}/Main");
      $settingsActionExists = $this->adios->actionExists("Plugins/{$pluginName}/Settings");

      $pluginObject = $this->adios->getPlugin($pluginName);
      $manifest = $pluginObject->manifest();

      if (!empty($manifest['faIcon'])) {
        $logoHtml = "
          <i
            class='{$manifest['faIcon']}'
            style='color:var(--cl-main);font-size:2em;cursor:pointer;'
            onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
          ></i>
        ";
      } else if (!empty($manifest['logo'])) {
        $logoHtml = "
          <img
            src='{$this->adios->config['url']}/adios/assets/plugins/{$pluginName}/~/{$manifest['logo']}'
            style='max-width:100%;max-height:100%;cursor:pointer'
            onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
          />
        ";
      } else {
        $logoHtml = "";
      }

      $cardsHtml .= "
        <div class='col-12'>
          <div class='card shadow mb-2 ".($this->adios->isPluginEnabled($pluginName) ? "" : "bg-secondary")."'>
            <div class='card-body'>
              <div style='display:flex;align-items: center;'>
                ".(empty($logoHtml) ? "" : "
                  <div style='flex-basis:120px;height:2em;margin-right:2em;'>{$logoHtml}</div>
                ")."
                <div style='flex:1;color:var(--cl-main);'>
                  <b class='plugin-title'>".hsc($this->translate($pluginTitle))."</b>
                  <div class='plugin-name small' style='color:#AAAAAA'>".hsc($pluginName)."</div>
                </div>
                <div style='text-align:right'>
                  ".($mainActionExists ? "
                    <a
                      href='javascript:void(0)'
                      class='btn btn-primary'
                      onclick='desktop_render(\"Plugins/{$pluginName}/Main\");'
                    >".$this->translate("Manage")."</a>
                  " : "")."
                  ".($settingsActionExists ? "
                    <a
                      href='javascript:void(0)'
                      class='btn btn-primary'
                      onclick='window_render(\"Plugins/{$pluginName}/Settings\");'
                    >".$this->translate("Settings")."</a>
                  " : "")."
                  ".($this->adios->isPluginEnabled($pluginName) ? "
                    <a
                      href='javascript:void(0)'
                      class='btn btn-danger'
                      onclick='
                        if (confirm(\"".$this->translate("Are you sure to disable this plugin?")."\")) {
                          _ajax_read(
                            \"Plugins/Disable\",
                            {\"plugin\": \"".ads($pluginName)."\"},
                            function(res) {
                              desktop_render(\"Plugins/Overview\");
                            }
                          );
                        }
                      '
                    >".$this->translate("Disable")."</a>
                  " : "
                    <a
                      href='javascript:void(0)'
                      class='btn btn-success'
                      onclick='
                        _ajax_read(
                          \"Plugins/Enable\",
                          {\"plugin\": \"".ads($pluginName)."\"},
                          function(res) {
                            desktop_render(\"Plugins/Overview\");
                          }
                        );
                      '
                    >".$this->translate("Enable")."</a>
                  ")."
                </div>
              </div>
            </div>
          </div>
        </div>
      ";
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
          <div class='col-xl-6 col-12 mb-4'>
            <div class='input-group'>
              <input
                type='text'
                id='{$this->uid}_search'
                class='form-control'
                value=''
                placeholder='".$this->translate("Search plugins...")."'
                onkeyup='{$this->uid}_searchPlugins();'
              />
              <div class='input-group-append'>
                <button class='btn btn-primary' type='button' onclick='{$this->uid}_searchPlugins();'>
                  <i class='fas fa-search fa-sm'></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div id='{$this->uid}_plugins' class='row'>
          {$cardsHtml}
        </div>

        <script>
          function {$this->uid}_searchPlugins() {
            let q = $('#{$this->uid}_search').val();
            let plugins = $('#{$this->uid}_plugins .card');

            if (q == '') {
              plugins.show();
            } else {
              plugins.hide();

              plugins.each(function() {
                let pluginTitle = $(this).find('.plugin-title').get(0).innerText;
                let pluginName = $(this).find('.plugin-name').get(0).innerText;

                if (
                  pluginTitle.toLowerCase().indexOf(q.toLowerCase()) !== -1
                  || pluginName.toLowerCase().indexOf(q.toLowerCase()) !== -1
                ) {
                  $(this).show();
                }
              });
            }
          }
        </script>
      ";
    }

    return $html;
  }
}