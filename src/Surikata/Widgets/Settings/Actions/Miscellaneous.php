<?php

namespace ADIOS\Actions\Settings;

class Miscellaneous extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["miscellaneous"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "miscellaneous",
      "title" => "Miscellaneous settings",
      "template" => [
        "tabs" => [
          [
            "title" => "Shopping cart",
            "items" => [
              [
                "title" => "Days to abandon shopping cart",
                "description" => "Number of days after which the shopping cart will be treated as abanoned.",
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_shoppingCartDaysToAbandon",
                  "value" => $settings['shoppingCartDaysToAbandon'],
                  "unit" => "day(s)",
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}