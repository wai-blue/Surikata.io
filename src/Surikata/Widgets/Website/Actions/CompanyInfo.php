<?php

namespace ADIOS\Actions\Website;

class CompanyInfo extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["companyInfo"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/companyInfo",
      "title" => "{$this->params['domainName']} » Company Info",
      "template" => [
        "tabs" => [
          [
            "title" => "General",
            "items" => [
              [
                "title" => "Slogan",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_slogan",
                  "value" => $settings['slogan'],
                ]),
              ],
              [
                "title" => "Logo",
                "input" => $this->adios->ui->Input([
                  "type" => "image",
                  "uid" => "{$this->uid}_logo",
                  "value" => $settings['logo'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Headquarter",
            "items" => [
              [
                "title" => "Street, 1st line",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterStreet1",
                  "value" => $settings['headquarterStreet1'],
                ]),
              ],
              [
                "title" => "Street, 2st line",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterStreet2",
                  "value" => $settings['headquarterStreet2'],
                ]),
              ],
              [
                "title" => "ZIP",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterZIP",
                  "value" => $settings['headquarterZIP'],
                ]),
              ],
              [
                "title" => "City",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCity",
                  "value" => $settings['headquarterCity'],
                ]),
              ],
              [
                "title" => "Region",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterRegion",
                  "value" => $settings['headquarterRegion'],
                ]),
              ],
              [
                "title" => "Country",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCountry",
                  "value" => $settings['headquarterCountry'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Contact information",
            "items" => [
              [
                "title" => "Contact phone number",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactPhoneNumber",
                  "value" => $settings['contactPhoneNumber'],
                ]),
              ],
              [
                "title" => "Contact email",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactEmail",
                  "value" => $settings['contactEmail'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Social networks",
            "items" => [
              [
                "title" => "Facebook page URL",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlFacebook",
                  "value" => $settings['urlFacebook'],
                ]),
              ],
              [
                "title" => "Twitter URL",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlTwitter",
                  "value" => $settings['urlTwitter'],
                ]),
              ],
              [
                "title" => "Instagram profile URL",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlInstagram",
                  "value" => $settings['urlInstagram'],
                ]),
              ],
              [
                "title" => "YouTube channel URL",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlYouTube",
                  "value" => $settings['urlYouTube'],
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}