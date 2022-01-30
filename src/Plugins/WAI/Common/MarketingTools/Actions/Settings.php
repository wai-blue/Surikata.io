<?php

namespace ADIOS\Actions\Plugins\WAI\Common\MarketingTools;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Common/MarketingTools");

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
            "title" => $this->translate("Google Analytics Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_googleAnalyticsCode",
              "value" => $settings["{$domain['name']}_googleAnalyticsCode"],
            ]),
          ],
          [
            "title" => $this->translate("Google Tag Manager Code"),
            "input" => $this->adios->ui->Input([
              "type" => "text",
              "uid" => "{$this->uid}_{$domain['name']}_googleTagManagerCode",
              "value" => $settings["{$domain['name']}_googleTagManagerCode"],
            ]),
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
      "settings_group" => "plugins/WAI/Common/MarketingTools",
      "title" => $this->translate("Marketing tools settings"),
      "template" => [
        "tabs" => $tabs,
      ],
    ]);
  }
}