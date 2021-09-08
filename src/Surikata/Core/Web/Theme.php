<?php

namespace Surikata\Core\Web;

class Theme {
  var $surikataWidgetWebsite;
  var $params = [];
  var $name = "";

  public function __construct($surikataWidgetWebsite, $params = []) {
    $this->name = str_replace("\\", "/", str_replace("Surikata\\Themes\\", "", get_class($this)));
    $this->params = $params;
    $this->adminPanelWidgetWebsite = &$surikataWidgetWebsite;
  }

  public function getLayouts() {
    $layouts = [];
    foreach (scandir("{$this->adminPanelWidgetWebsite->adios->config['themes_dir']}/{$this->name}/Layouts") as $file) {
      if (!in_array($file, [".", ".."])) {
        $layouts[] = str_replace(".php", "", $file);
      }
    }

    return $layouts;
  }

  public function getLayout($layoutName) {
    $layoutClassName = "\\Surikata\\Themes\\{$this->name}\\Layouts\\".str_replace("/", "\\", $layoutName);
    if (class_exists($layoutClassName)) {
      return new $layoutClassName($this);
    } else {
      return NULL;
    }
  }

  public function getDefaultColorsAndStyles() {
    return [];
  }
}