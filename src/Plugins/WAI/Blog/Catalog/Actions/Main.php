<?php

namespace ADIOS\Actions\Plugins\WAI\Blog\Catalog;

class Main extends \ADIOS\Core\Plugin\Action {
  public function render() {
    return "
      <h1>".$this->translate("Blogs")."</h1>

      <div class='row'>
        <div class='col'>
          ".$this->adios->ui->button([
            "fa_icon" => "fas fa-table",
            "class" => "btn-secondary btn-icon-split",
            "text" => $this->translate("Blogs"),
            "onclick" => "desktop_update('Website/Blog');",
          ])->render()."<br/>
          <br/>
          ".$this->adios->ui->button([
            "fa_icon" => "fas fa-table",
            "class" => "btn-secondary btn-icon-split",
            "text" => $this->translate("Tags"),
            "onclick" => "desktop_update('Website/Blog/Tags');",
          ])->render()."
        </div>
      </div>
    ";
  }
}
