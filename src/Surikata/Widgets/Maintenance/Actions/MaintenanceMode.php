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
              "uid" => "{$this->uid}_activated",
              "value" => $settings['activated'],
            ]),
            "description" => "When checked, a maintenance message will be displayed to your visitors.",
          ],
          [
            "title" => "Additional information",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_additionalInfo",
              "value" => $settings['additionalInfo'],
            ]),
            "description" => "Enter the message that will be displayed to your visitors while the maintenance mode is activated.",
          ],
        ],
      ],
    ]);
  }
}