<?php

namespace ADIOS\Actions\Settings;

class Maitenance extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"]["maintenance"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/maintenance",
      "title" => "Maintenance Mode",
      "template" => [
        "items" => [
          [
            "title" => "Turn on maintenance mode",
            "input" => $this->adios->ui->Input([
              "type" => "boolean",
              "uid" => "{$this->uid}_mod_udrzby",
              "value" => $settings['mod_udrzby'],
            ]),
            "description" => "When checked, a maintenance notification will appear on the web.",
          ],
          [
            "title" => "Supplementary notice",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_mod_udrzby_doplnujuci_oznam",
              "value" => $settings['mod_udrzby_doplnujuci_oznam'],
            ]),
            "description" => "Enter the message that appears when the maintenance mode is on.",
          ],
        ],
      ],
    ]);
  }
}