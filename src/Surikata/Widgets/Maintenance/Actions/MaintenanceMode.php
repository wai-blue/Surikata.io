<?php

namespace ADIOS\Actions\Maintenance;

class MaintenanceMode extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"]["maintenance"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/maintenance",
      "title" => $this->translate("Maintenance Mode"),
      "template" => [
        "items" => [
          [
            "title" => $this->translate("Activate maintenance mode"),
            "input" => $this->adios->ui->Input([
              "type" => "boolean",
              "uid" => "{$this->uid}_activated",
              "value" => $settings['activated'],
            ]),
            "description" => $this->translate("When checked, a maintenance message will be displayed to your visitors."),
          ],
          [
            "title" => $this->translate("Additional information"),
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_additionalInfo",
              "value" => $settings['additionalInfo'],
            ]),
            "description" => $this->translate("Enter the message that will be displayed to your visitors while the maintenance mode is activated."),
          ],
        ],
      ],
    ]);
  }
}