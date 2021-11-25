<?php

namespace ADIOS\Actions\Website;

class OnlineMarketingAndSEO extends \ADIOS\Core\Widget\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["onlineMarketingAndSEO"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/onlineMarketingAndSEO",
      "title" => "{$this->params['domainName']} » ".$this->translate("Online marketing and SEO"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("Basic SEO parameters"),
            "items" => [
              [
                "title" => $this->translate("Keywords"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_keywords",
                  "value" => $settings['keywords'],
                ]),
                "description" => $this->translate("It will be used if the active page does not have a minimum value set."),
              ],
              [
                "title" => $this->translate("Description"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_description",
                  "value" => $settings['description'],
                ]),
                "description" => $this->translate("It will be used if the active page does not have a minimum value set."),
              ],
            ],
          ],
          [
            "title" => $this->translate("Google Analytics"),
            "items" => [
              [
                "title" => $this->translate("Google Analytics"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_googleAnalytics",
                  "value" => $settings['googleAnalytics'],
                ]),
              ],
            ],
          ],
          [
            "title" => $this->translate("Google Tag Manager"),
            "items" => [
              [
                "title" => $this->translate("Google Tag Manager"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_googleTagManager",
                  "value" => $settings['googleTagManager'],
                ]),
              ],
            ],
          ],
          [
            "title" => $this->translate("Facebook Pixel"),
            "items" => [
              [
                "title" => $this->translate("Facebook Pixel"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_facebookPixel",
                  "value" => $settings['facebookPixel'],
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}