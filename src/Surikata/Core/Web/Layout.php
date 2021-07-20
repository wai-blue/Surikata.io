<?php

namespace Surikata\Core\Web;

class Layout {
  var $theme;
  var $params = [];
  var $name = "";

  public function __construct($theme, $params = []) {
    $this->name = str_replace("\\", "/", str_replace("Surikata\\Themes\\", "", get_class($this)));
    $this->params = $params;
    $this->theme = &$theme;
  }

  public function getPreviewHtml() {
    return "getPreviewHtmlNotOverriden";
  }

}