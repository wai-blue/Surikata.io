<?php

namespace Surikata\Core\Web;

class Theme {
  var $adminPanelWidgetWebsite = null;
  var $params = [];
  var $name = "";

  public function __construct($adminPanelWidgetWebsite, $params = []) {
    $this->name = str_replace("\\", "/", str_replace("Surikata\\Themes\\", "", get_class($this)));
    $this->params = $params;
    $this->adminPanelWidgetWebsite = &$adminPanelWidgetWebsite;

    $this->myRootFolder = dirname((new \ReflectionClass(get_class($this)))->getFileName());
  }

  public function getLayouts() {
    $layouts = [];
    foreach (scandir("{$this->myRootFolder}/Layouts") as $file) {
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

  public function onAfterInstall() {
  }
}