<?php

namespace ADIOS\Actions\Website\ContentStructure;

class PluginSettings extends \ADIOS\Core\Widget\Action {
  public function render() {
    $contentStructure = @json_decode($this->params['contentStructure'], TRUE);
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
            <select
              id='{$this->uid}_plugin'
              style='width:100%;font-size:1.5em;'
              size=15
              onchange='
                let pluginUID = $(this).find(\"option:selected\").data(\"plugin-uid\");
                $(\".surikata-theme-plugin\").hide();
                $(\"#{$this->uid}_plugin_\" + pluginUID).show();
              '
            >
              <option value=''>-- No plugin here --</option>
    ";
    foreach ($this->adios->getPlugins() as $pluginName => $plugin) {
      $pluginUID = \ADIOS\Core\HelperFunctions::str2uid($pluginName);

      $pluginSelectHtml .= "
        <option
          data-plugin-name='".ads($pluginName)."'
          data-plugin-uid='{$pluginUID}'
          ".($pluginName == $activatedPluginName ? "selected" : "")."
        >
          {$pluginName}
        </option>
      ";
    }
    $pluginSelectHtml .= "
            </select>
          </div>
        </div>
      </div>
    ";

    $pluginsSettingsHtml = "";

    foreach ($this->adios->getPlugins() as $pluginName => $plugin) {
      $pluginUID = \ADIOS\Core\HelperFunctions::str2uid($pluginName);

      $pluginsSettingsHtml .= "
        <div
          class='surikata-theme-plugin'
          id='{$this->uid}_plugin_{$pluginUID}'
          style='display:".($pluginName == $activatedPluginName ? "block" : "none")."'
        >
      ";

      $tmpPlugin = $this->adios->getPlugin($pluginName);
      $tmpInputUIDPrefix = "{$this->uid}_{$pluginUID}";

      if (is_object($tmpPlugin)) {
        if (method_exists($tmpPlugin, "getSettingsForWebsite")) {
          $availableSettings = $tmpPlugin->getSettingsForWebsite($tmpInputUIDPrefix, $pluginSettings);
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
          let pluginSelectedOption = $('#{$this->uid}_plugin option:selected');
          let pluginName = $(pluginSelectedOption).data('plugin-name');
          let pluginUID = $(pluginSelectedOption).data('plugin-uid');

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
        <div class='col-4'>
          <div class='h4'>
            Renderer plugin
          </div>
          {$pluginSelectHtml}
        </div>
        <div class='col-8'>
          <div class='h4'>
            Plugin settings
          </div>
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