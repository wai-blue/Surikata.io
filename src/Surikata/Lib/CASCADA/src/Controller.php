<?php

namespace CASCADA;

class Controller {
  var $cascada;
  var $params = [];

  function __construct($cascada, $params = []) {
    $this->cascada = &$cascada;
    $this->params = $params;
  }

  public function preRender() { }

  public function render() {
    // if string is returned, CASCADA will not continue in rendering and outputs the returned string
    return NULL;
  }

  public function postRender() { }

}

