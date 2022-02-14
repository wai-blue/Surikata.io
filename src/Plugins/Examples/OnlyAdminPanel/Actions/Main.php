<?php

namespace ADIOS\Actions\Plugins\Examples\OnlyAdminPanel;

class Main extends \ADIOS\Core\Plugin\Action {
  public function render() {
    return "
      <h1>".$this->translate('OnlyAdminPanel example')."</h1>

      Hello developer. This is the Main action of the OnlyAdminPanel example.
    ";
  }
}
