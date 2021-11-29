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
    $this->shortName = end(explode("/", $this->name));
    $this->adios = &$adios;
    $this->params = [];
    $this->gtp = $this->adios->gtp;

    $this->myRootFolder = str_replace("\\", "/", dirname((new \ReflectionClass(get_class($this)))->getFileName()));
    $this->dictionaryFolder = $this->myRootFolder."/Lang";

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

  public function manifest() {
    return [
      // "faIcon" => "fas fa-puzzle-piece",
      "logo" => "",
      "title" => $this->niceName ?? $this->name,
      "description" => "",
    ];
  }

  public function install(object $installer) {
    return TRUE;
  }

  public function installOnce(object $installer) {
    return TRUE;
  }

  public function loadModels() {
    foreach ($this->adios->pluginFolders as $pluginFolder) {
      $folder = $pluginFolder."/{$this->name}/Models";

      if (is_dir($folder)) {
        foreach (scandir($folder) as $file) {
          if (is_file("{$folder}/{$file}")) {
            $tmpModelName = str_replace(".php", "", $file);
            $this->adios->registerModel("Plugins/{$this->name}/Models/{$tmpModelName}");
          }
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
