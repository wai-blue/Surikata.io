<?php

namespace ADIOS\Actions\Plugins;

class Disable extends \ADIOS\Core\Widget\Action {

  public function render() {
    $plugin = $this->params["plugin"] ?: "";

    $this->adios->disablePlugin($plugin);
  }
}