<?php

namespace ADIOS\Actions\Plugins;

class Enable extends \ADIOS\Core\Widget\Action {

  public function render() {
    $plugin = $this->params["plugin"] ?: "";

    $this->adios->enablePlugin($plugin);
  }
}