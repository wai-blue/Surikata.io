<?php

namespace ADIOS\Widgets;

class Plugins extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ADMINISTRATOR)) {
      foreach ($this->adios->getPlugins() as $pluginName => $pluginObject) {
        if ($this->adios->actionExists("Plugins/{$pluginName}/Main")) {
          $pluginSettingsMenu[] = [
            "title" => $pluginObject->niceName,
            "onclick" => "desktop_update('Plugins/{$pluginName}/Main');",
          ];
        }
      }

      $this->adios->config['desktop']['sidebarItems']['Plugins'] = [
        "fa_icon" => "fas fa-puzzle-piece",
        "title" => "Plugins",
        "onclick" => "desktop_render('Plugins/Overview');",
        "sub" => $pluginSettingsMenu,
      ];
    }
  }

}