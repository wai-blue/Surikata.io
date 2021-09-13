<?php

namespace ADIOS\Actions\Maintenance;

class MaintenanceMode extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"]["maintenance"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/maintenance",
      "title" => "Maintenance Mode",
      "template" => [
        "items" => [
          [
            "title" => "Activate maintenance mode",
            "input" => $this->adios->ui->Input([
              "type" => "boolean",
              "uid" => "{$this->uid}_mod_udrzby",
              "value" => $settings['mod_udrzby'],
            ]),
            "description" => "When checked, a maintenance message will be displayed to your visitors.",
          ],
          [
            "title" => "Additional note",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_mod_udrzby_doplnujuci_oznam",
              "value" => $settings['mod_udrzby_doplnujuci_oznam'],
            ]),
            "description" => "Enter the message that will be displayed to your visitors while the maintenance mode is activated.",
          ],
        ],
      ],
    ]);
  }
}