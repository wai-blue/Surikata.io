<?php

namespace ADIOS\Actions\Products;

class Margins extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["sales"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "sales",
      "title" => "Margins",
      "template" => [
        "items" => [
          [
            "title" => "Global margin",
            "input" => $this->adios->ui->Input([
              "type" => "float",
              "uid" => "{$this->uid}_globalMargin",
              "value" => $settings['globalMargin'],
              "unit" => "%",
            ]),
            "description" => "The basic margin is applied to each product and at each sale.", //"Základná marža sa aplikuje pri každom produkte a pri každom predaji.",
          ],
          [
            "title" => "Product-based margins",
            "input" => $this->adios->ui->Button([
              "text" => "Manage product-pased margins",
              "fa_icon" => "fas fa-percentage",
              "onclick" => "window_render('Products/Prices/Margins')",
            ]),
            "description" => "Setting other margins according to selected purchase parameters.", // "Nastavenie ostatných marží podľa vybraných parametrov nákupu.",
          ],
        ],
      ],
    ]);
  }
}