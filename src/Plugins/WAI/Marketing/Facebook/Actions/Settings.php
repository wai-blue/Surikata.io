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
            "title" => $this->translate("Enable sitemap.xml"),
            "input" => $this->adios->ui->Input([
              "type" => "boolean",
              "uid" => "{$this->uid}_{$domain['name']}_enableSitemapXML",
              "value" => $settings["{$domain['name']}_enableSitemapXML"],
            ]),
            "description" => $this->translate("If checked, the sitemap.xml URL will be published.")
          ],
          [
            "title" => $this->translate("Facebook Pixel Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_facebookPixelCode",
              "value" => $settings["{$domain['name']}_facebookPixelCode"],
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