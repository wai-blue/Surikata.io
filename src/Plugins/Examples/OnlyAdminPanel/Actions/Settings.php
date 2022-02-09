<?php

namespace ADIOS\Actions\Plugins\Examples\OnlyAdminPanel;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("Examples/OnlyAdminPanel");

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/Examples/OnlyAdminPanel",
      "title" => $this->translate("Examples OnlyAdminPanel - Settings"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("Example settings"),
            "items" => [
              [
                "title" => $this->translate("Example boolean setting"),
                "input" => $this->adios->ui->Input([
                  "type" => "boolean",
                  "uid" => "{$this->uid}_exampleBoolean",
                  "value" => $settings['exampleBoolean'],
                ]),
                "description" => $this->translate("This is a sample boolean setting.")
              ],
              [
                "title" => $this->translate("Example number setting"),
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_exampleNumber",
                  "value" => $settings['exampleNumber'],
                ]),
                "description" => $this->translate("This is a sample number setting.")
              ],
              [
                "title" => $this->translate("Example single-line setting"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_exampleSingleLine",
                  "value" => $settings['exampleSingleLine'],
                ]),
                "description" => $this->translate("This is a sample single-line setting.")
              ],
              [
                "title" => $this->translate("Example multi-line setting"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_exampleMultiLine",
                  "value" => $settings['exampleMultiLine'],
                ]),
                "description" => $this->translate("This is a sample multi-line setting.")
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}