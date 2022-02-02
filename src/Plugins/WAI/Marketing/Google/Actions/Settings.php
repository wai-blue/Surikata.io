<?php

namespace ADIOS\Actions\Plugins\WAI\Marketing\Google;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Marketing/Google");

    $tabs = [];
    foreach ($this->adios->getAvailableDomains() as $domain) {
      $tabs[] = [
        "title" => $domain['description'],
        "items" => [
          [
            "title" => $this->translate("Google Analytics Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_googleAnalytics",
              "value" => $settings["{$domain['name']}_googleAnalytics"],
            ]),
          ],
          [
            "title" => $this->translate("Google Tag Manager Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_googleTagManager",
              "value" => $settings["{$domain['name']}_googleTagManager"],
            ]),
          ],
        ],
      ];
    }

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Marketing/Google",
      "title" => $this->translate("Google tools settings"),
      "template" => [
        "tabs" => $tabs,
      ],
    ]);
  }
}