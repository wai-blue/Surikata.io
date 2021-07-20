<?php

namespace ADIOS\Actions\WAI\Delivery\DPD;

class Settings extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["plugins"]["WAI/Delivery/DPD"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Delivery/DPD",
      "title" => "WAI/Delivery/DPD - Settings",
      "template" => [
        "tabs" => [
          [
            "title" => "Price",
            "items" => [
              [
                "title" => "Base delivery price",
                "input" => $this->adios->ui->Input([
                  "type" => "float",
                  "uid" => "{$this->uid}_basePrice",
                  "value" => $settings['basePrice'],
                  "unit" => "EUR",
                ]),
              ],
              [
                "title" => "Minimum order value for receiving discount",
                "input" => $this->adios->ui->Input([
                  "type" => "float",
                  "uid" => "{$this->uid}_minimumOrderValueForDiscount",
                  "value" => $settings['minimumOrderValueForDiscount'],
                  "unit" => "EUR",
                ]),
              ],
              [
                "title" => "Disvount rate",
                "input" => $this->adios->ui->Input([
                  "type" => "float",
                  "uid" => "{$this->uid}_discountRate",
                  "value" => $settings['discountRate'],
                  "unit" => "%",
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}