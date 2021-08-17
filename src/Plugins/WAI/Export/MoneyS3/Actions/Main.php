<?php

namespace ADIOS\Actions\Plugins\WAI\Export\MoneyS3;

class Main extends \ADIOS\Core\Action {
  public function render() {
    return "
      <h1>MoneyS3 plugin</h1>
      ".$this->adios->ui->button([
        "fa_icon" => "fas fa-cog",
        "text" => "Settings",
        "onclick" => "
          window_render('Plugins/WAI/Export/MoneyS3/Settings');
        ",
      ])->render()."
    ";
  }
}