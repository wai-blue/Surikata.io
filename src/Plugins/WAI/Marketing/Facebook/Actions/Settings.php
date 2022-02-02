<?php

namespace ADIOS\Actions\Plugins\WAI\Marketing\Facebook;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Marketing/Facebook");

    $tabs = [];
    foreach ($this->adios->getAvailableDomains() as $domain) {
      $tabs[] = [
        "title" => $domain['description'],
        "items" => [
          [
            "title" => $this->translate("Facebook Pixel Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_pixel",
              "value" => $settings["{$domain['name']}_pixel"],
            ]),
          ],
        ],
      ];
    }

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Marketing/Facebook",
      "title" => $this->translate("Facebook tools settings"),
      "template" => [
        "tabs" => $tabs,
      ],
    ]);
  }
}