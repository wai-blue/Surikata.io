<?php

namespace ADIOS\Actions\Plugins\WAI\Misc\Slideshow;

class Main extends \ADIOS\Core\Action {
  public function render() {
    return "
      <h1>Slideshow</h1>

      ".$this->adios->ui->button([
        "fa_icon" => "fas fa-table",
        "class" => "btn-secondary btn-icon-split",
        "text" => "Slideshow",
        "onclick" => "desktop_update('Website/Slideshow');",
      ])->render()."
    ";
  }
}
