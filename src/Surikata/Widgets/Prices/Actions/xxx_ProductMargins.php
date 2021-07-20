<?php

namespace ADIOS\Actions\Prices;

class ProductMargins extends \ADIOS\Core\Action {
  public function render() {
    $window_content_html = $this->adios->renderAction("UI/Table", [
      "model" => "Widgets/Prices/Models/ProductMargin",
      "show_title" => FALSE,
    ]);

    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "content" => $window_content_html,
      "title" => "Product-based margins",
      "header" => [
        $this->adios->ui->Button(["type" => "close", "onclick" => "window_close('{$this->uid}_window');"]),
        $this->adios->ui->Button(["type" => "add", "onclick" => "window_render('Prices/ProductMargins/Add');"]),
      ]
    ])->render();
  }
}