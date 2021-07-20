<?php

namespace ADIOS\Actions\Plugins\WAI\News;

class Main extends \ADIOS\Core\Action {
  public function render() {
    return "
      <h1>News</h1>

      ".$this->adios->ui->button([
        "fa_icon" => "fas fa-table",
        "class" => "btn-secondary btn-icon-split",
        "text" => "News",
        "onclick" => "desktop_update('Website/News');",
      ])->render()."
    ";
  }
}
