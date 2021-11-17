<?php

namespace ADIOS\Actions\Plugins\WAI\Misc\Slideshow;

class Main extends \ADIOS\Core\Plugin\Action {
  public function render() {
    return "
      <h1>".$this->translate('Slideshow')."</h1>

      ".$this->adios->ui->button([
        "fa_icon" => "fas fa-table",
        "class" => "btn-secondary btn-icon-split",
        "text" => $this->translate("Slideshow"),
        "onclick" => "desktop_update('Website/Slideshow');",
      ])->render()."
    ";
  }
}
