<?php

namespace ADIOS\Actions\Plugins\WAI\News;

class Main extends \ADIOS\Core\Action {
  public function render() {
    return "
      <h1>".$this->translate('News')."</h1>

      ".$this->adios->ui->button([
        "fa_icon" => "fas fa-table",
        "class" => "btn-secondary btn-icon-split",
        "text" => $this->translate("News"),
        "onclick" => "desktop_update('Website/News');",
      ])->render()."
    ";
  }
}
