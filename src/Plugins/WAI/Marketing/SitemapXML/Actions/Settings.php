<?php

namespace ADIOS\Actions\Plugins\WAI\Marketing\SitemapXML;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Marketing/SitemapXML");

    $tabs = [];
    foreach ($this->adios->getAvailableDomains() as $domain) {
      $tabs[] = [
        "title" => $domain['description'],
        "items" => [
          [
            "title" => $this->translate("Enable sitemap.xml"),
            "input" => $this->adios->ui->Input([
              "type" => "boolean",
              "uid" => "{$this->uid}_{$domain['name']}_enableSitemapXML",
              "value" => $settings["{$domain['name']}_enableSitemapXML"],
            ]),
            "description" => $this->translate("If checked, the sitemap.xml URL will be published.")
          ]
        ],
      ];
    }

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Marketing/Google",
      "title" => $this->translate("SitemapXML"),
      "template" => [
        "tabs" => $tabs,
      ],
    ]);
  }
}