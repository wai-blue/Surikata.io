<?php

namespace ADIOS\Actions\Website\ContentStructure;

class PluginSettings extends \ADIOS\Core\Widget\Action {
  public function render() {
    
    $idWebPage = (int) $this->params['idWebPage'];
    $contentStructureRaw = $this->params['contentStructure'] ?? "";

    if ($idWebPage > 0 && empty($contentStructureRaw)) {
      $modelWebPage = new \ADIOS\Widgets\Website\Models\WebPage($this->adios);
      $webPage = $modelWebPage->getById($idWebPage);

      $contentStructureRaw = $webPage['content_structure'];
    }
    
    $contentStructure = @json_decode($contentStructureRaw, TRUE);
    $themeName = $this->adios->config['settings']['web'][$this->params['domain']]['design']['theme'];
    $layoutName = $contentStructure['layout'];
    $panelName = $this->params['panelName'];
    $activatedPluginName = $contentStructure['panels'][$panelName]['plugin'];
    $pluginSettings = $contentStructure['panels'][$panelName]['settings'];

    $theme = $this->adios->widgets['Website']->themes[$themeName];
    $layout = $theme->getLayout($layoutName);

    $pluginSelectHtml = "
      <div class='adios ui form table'>
        <div class='adios ui form subrow'>
          <div class='adios ui form form_title'>
            Choose a plugin
          </div>
          <div class='adios ui form form_input'>
            <div id='{$this->uid}_plugin_select_div'>
              <div
                data-plugin-name='".ads($pluginName)."'
                data-plugin-uid='{$pluginUID}'
                class='item ".($activatedPluginName == "" ? "selected" : "")."'
              >
                -- No plugin for this panel --
              </div>
    ";
    foreach ($this->adios->getPlugins() as $pluginName => $pluginObject) {
      $pluginUID = \ADIOS\Core\HelperFunctions::str2uid($pluginName);

      $manifest = $pluginObject->manifest();

      $pluginSelectHtml .= "
        <div
          data-plugin-name='".ads($pluginName)."'
          data-plugin-uid='{$pluginUID}'
          class='item ".($activatedPluginName == $pluginName ? "selected" : "")."'
        >
          <div class='title'>".hsc($manifest['title'])."</div>
          <div class='subtitle'>{$pluginName}</div>
        </div>
      ";
    }
    $pluginSelectHtml .= "
            </div>
          </div>
        </div>
      </div>
      <script>
        $('#{$this->uid}_plugin_select_div .item').click(function() {
          let pluginUID = $(this).data('plugin-uid');

          $('#{$this->uid}_plugin_select_div > .item').removeClass('selected');
          $(this).addClass('selected');

          $('.surikata-theme-plugin').hide();
          $('#{$this->uid}_plugin_' + pluginUID).show();
        });
      </script>
      <style>
        #{$this->uid}_plugin_select_div {
        }

        #{$this->uid}_plugin_select_div .item {
          cursor: pointer;
          padding: 0.25em;
          border: 1px solid transparent;
          box-sizing: border-box;
          margin-right: 5px;
        }

        #{$this->uid}_plugin_select_div .item:hover {
          border-color: var(--cl-main);
        }

        #{$this->uid}_plugin_select_div .item.selected {
          background: var(--cl-main);
          color: white;
        }

        #{$this->uid}_plugin_select_div .item .subtitle {
          font-size: 0.5em;
        }

      </style>
    ";

    $pluginsSettingsHtml = "";

    foreach ($this->adios->getPlugins() as $pluginName => $pluginObject) {
      $pluginUID = \ADIOS\Core\HelperFunctions::str2uid($pluginName);

      $manifest = $pluginObject->manifest();

      $pluginsSettingsHtml .= "
        <div
          class='surikata-theme-plugin'
          id='{$this->uid}_plugin_{$pluginUID}'
          style='display:".($pluginName == $activatedPluginName ? "block" : "none")."'
        >

        <h3>".hsc($manifest['title'])."</h3>
      ";

      $tmpInputUIDPrefix = "{$this->uid}_{$pluginUID}";

      if (is_object($pluginObject)) {
        if (method_exists($pluginObject, "getSettingsForWebsite")) {
          $availableSettings = $pluginObject->getSettingsForWebsite($tmpInputUIDPrefix, $pluginSettings);
        } else {
          $availableSettings = [];
        }

        $settingsItems = [];
        foreach ($availableSettings as $settingName => $inputParams) {
          $inputParams["uid"] = "{$tmpInputUIDPrefix}_{$settingName}";
          $inputParams["value"] = $pluginSettings[$settingName];

          if (empty($inputParams["input"])) {
            $input = $this->adios->ui->Input($inputParams);
          } else {
            $input = $inputParams["input"];
          }

          $settingsItems[] = [
            "title" => $inputParams["title"],
            "description" => $inputParams["description"],
            "input" => $input,
          ];
        }

        if (count($settingsItems) == 0) {
          $pluginsSettingsHtml .= "
            <div>
              This plugin has no settings.
            </div>
          ";
        } else {
          $pluginsSettingsHtml .=  (new \ADIOS\Core\UI\Input\SettingsPanel(
            $this->adios,
            "{$tmpInputUIDPrefix}_",
            [
              "settings_group" => "web/plugins/{$pluginName}",
              "title" => $pluginName,
              "template" => [
                "items" => $settingsItems,
              ],
            ]
          ))->render();
        }

      }

      $pluginsSettingsHtml .= "
        </div>
      ";
    }

    $windowContentHtml = "
      <script>
        function {$this->uid}_close(res) {
          window_close('{$this->uid}_window', res);
        }

        function {$this->uid}_save() {
          let pluginSelected = $('#{$this->uid}_plugin_select_div > .item.selected');
          let pluginName = $(pluginSelected).data('plugin-name');
          let pluginUID = $(pluginSelected).data('plugin-uid');

          let data = { 'plugin': pluginName, 'settings': {} };

          if ($('#{$this->uid}_plugin_' + pluginUID).length > 0) {
            data['settings'] = ui_form_get_values(
              '{$this->uid}_plugin_' + pluginUID,
              '{$this->uid}_' + pluginUID + '_',
            );
          }

          {$this->uid}_close(data);
        }

      </script>
      <div id='{$this->uid}_wrapper' class='row'>
        <div class='col-4 p-0' style='height: calc(100vh - 200px);overflow: auto;'>
          {$pluginSelectHtml}
        </div>
        <div class='col-8 p-0 pl-4' style='height: calc(100vh - 200px);overflow: auto;'>
          {$pluginsSettingsHtml}
        </div>
      </div>
      <script>
        $('#{$this->uid}_wrapper *[data-panel-name=\"{$panelName}\"]').addClass('selected');
      </script>
    ";

    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "content" => $windowContentHtml,
      "titleRaw" => "Panel <i>".hsc($this->params['panelName'])."</i>",
      "header" => [
        $this->adios->ui->Button(["type" => "close", "onclick" => "{$this->uid}_close()"]),
        $this->adios->ui->Button(["type" => "apply", "onclick" => "{$this->uid}_save();"]),
      ]
    ])->render();
  }
}