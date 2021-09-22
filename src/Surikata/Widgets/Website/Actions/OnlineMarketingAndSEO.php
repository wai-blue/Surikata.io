<?php

namespace ADIOS\Actions\Website;

class OnlineMarketingAndSEO extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["onlineMarketingAndSEO"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/onlineMarketingAndSEO",
      "title" => "{$this->params['domainName']} » Online marketing and SEO",
      "template" => [
        "tabs" => [
          [
            "title" => "Basic SEO parameters",
            "items" => [
              [
                "title" => "Keywords",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_keywords",
                  "value" => $settings['keywords'],
                ]),
                "description" => "Bude použité, ak aktívna stránka nebude mať nastavenú vlastnú hodnotu.",
              ],
              [
                "title" => "Description",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_description",
                  "value" => $settings['description'],
                ]),
                "description" => "Bude použité, ak aktívna stránka nebude mať nastavenú vlastnú hodnotu.",
              ],
            ],
          ],
          [
            "title" => "Google Analytics",
            "items" => [
              [
                "title" => "Google Analytics",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_googleAnalytics",
                  "value" => $settings['googleAnalytics'],
                ]),
              ],
            ],
          ],
          [
            "title" => "Google Tag Manager",
            "items" => [
              [
                "title" => "Google Tag Manager",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_googleTagManager",
                  "value" => $settings['googleTagManager'],
                ]),
              ],
            ],
          ],
          [
            "title" => "Facebook Pixel",
            "items" => [
              [
                "title" => "Facebook Pixel",
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