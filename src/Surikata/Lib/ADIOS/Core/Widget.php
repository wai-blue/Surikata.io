<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

/*
  * ...
  * 
  */

class Widget {
  public $adios;
  public $gtp;
  // public $languageDictionary = [];

  public $params = [];
  public $models = [];

  function __construct($adios, $params = []) {
    $this->name = str_replace("ADIOS\\Widgets\\", "", get_class($this));
    $this->shortName = end(explode("/", $this->name));
    $this->adios = &$adios;
    $this->params = $params;
    $this->gtp = $this->adios->gtp;

    $this->myRootFolder = str_replace("\\", "/", dirname((new \ReflectionClass(get_class($this)))->getFileName()));

    if (!is_array($this->params)) {
      $this->params = [];
    }

    // preklady
    // $this->languageDictionary = $this->adios->loadLanguageDictionary($this);

    // inicializacia widgetu
    $this->init();

    $this->adios->dispatchEventToPlugins("onWidgetAfterInit", [
      "widget" => $this,
    ]);

    // nacitanie modelov
    $this->loadModels();

    $this->adios->dispatchEventToPlugins("onWidgetModelsLoaded", [
      "widget" => $this,
    ]);

  }

  public function init() {
    // to be overriden
    // desktop shortcuts, routing, ...
  }

  public function translate($string) {
    return $this->adios->translate($string, $this);
  }

  public function install() {
    return TRUE;
  }

  public function loadModels() {
    $dir = ADIOS_WIDGETS_DIR."/{$this->name}/Models";

    if (is_dir($dir)) {
      foreach (scandir($dir) as $file) {
        if (is_file("{$dir}/{$file}")) {
          $tmpModelName = str_replace(".php", "", $file);
          $this->adios->registerModel("Widgets/{$this->name}/Models/{$tmpModelName}");
        }
      }
    }

    $this->adios->dispatchEventToPlugins("onWidgetAfterModelsLoaded", [
      "widget" => $this,
    ]);
  }

}