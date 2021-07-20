<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Plugin {
  /**
   * Full name of the plugin. Useful for getPlugin() function
   *
   * @var mixed
   */
  public $name;

  /**
   * Reference to ADIOS object
   *
   * @var mixed
   */
  public $adios;

  public function __construct($adios) {
    $this->name = str_replace("\\", "/", str_replace("ADIOS\\Plugins\\", "", get_class($this)));
    $this->adios = &$adios;
    $this->gtp = $this->adios->gtp;

    // inicializacia pluginu
    $this->init();

    $this->adios->dispatchEventToPlugins("onPluginAfterInit", [
      "plugin" => $this,
    ]);

    // nacitanie modelov
    $this->loadModels();

    $this->adios->dispatchEventToPlugins("onPluginModelsLoaded", [
      "plugin" => $this,
    ]);

    // add routing
    $this->adios->addRouting($this->routing());
  }

  public function init() {
    // to be overriden
    // desktop shortcuts, routing, ...
  }

  public function install() {
    return TRUE;
  }

  public function loadModels() {
    $dir = ADIOS_PLUGINS_DIR."/{$this->name}/Models";

    if (is_dir($dir)) {
      foreach (scandir($dir) as $file) {
        if (is_file("{$dir}/{$file}")) {
          $tmpModelName = str_replace(".php", "", $file);
          $this->adios->models[] = "Plugins/{$this->name}/Models/{$tmpModelName}";
        }
      }
    }
  }

  public function routing(array $routing = []) {
    return $this->adios->dispatchEventToPlugins("onPluginAfterRouting", [
      "model" => $this,
      "routing" => $routing,
    ])["routing"];
  }

}
