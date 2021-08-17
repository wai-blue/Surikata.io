<?php

namespace ADIOS\Actions\Plugins\WAI\Export\Heureka;

class Settings extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["plugins"]["WAI"]["Export"]["Heureka"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Export/Heureka",
      "title" => "WAI/Export/Heureka - Settings",
      "template" => [
        "items" => [
          (empty($settings['secretAPIKey']) ? "
            <div class='alert alert-danger' role='alert'>
              Nemáte nastavený Tajný API Kľúč pre Heureka API.
            </div>
          " : NULL),
          [
            "title" => "Tajný API Kľúč",
            "description" => "Používa sa pre službu 'Overené zákazníkmi'.",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_secretAPIKey",
              "value" => $settings['secretAPIKey'],
            ]),
          ],
          [
            "title" => "Verejný API Kľúč",
            "description" => "Používa sa pre službu 'Meranie konverzií'.",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_publicAPIKey",
              "value" => $settings['publicAPIKey'],
            ]),
          ],
          [
            "title" => "Verzia Heureka servera",
            "input" => $this->adios->ui->Input([
              "type" => "varchar",
              "uid" => "{$this->uid}_serverVersion",
              "value" => $settings['serverVersion'],
              "enum_values" => [
                "sk" => "heureka.sk",
                "cz" => "heureka.cz",
              ],
            ]),
          ],
        ],
      ],
    ]);
  }
}