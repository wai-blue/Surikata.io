<?php

namespace ADIOS\Actions\Plugins\WAI\Export\MoneyS3;

class Settings extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["plugins"]["WAI"]["Export"]["MoneyS3"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Export/MoneyS3",
      "title" => "WAI/Export/MoneyS3 - Settings",
      "template" => [
        "items" => [
          [
            "title" => "Output XML file for products",
            "description" => "Relative path to the root folder of the project",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_outputFileProducts",
              "value" => $settings['outputFileProducts'],
            ]),
          ],
          [
            "title" => "Output XML file for orders",
            "description" => "Relative path to the root folder of the project",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_outputFileOrders",
              "value" => $settings['outputFileOrders'],
            ]),
          ],
        ],
      ],
    ]);
  }
}