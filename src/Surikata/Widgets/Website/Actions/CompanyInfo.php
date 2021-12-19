<?php

namespace ADIOS\Actions\Website;

class CompanyInfo extends \ADIOS\Core\Widget\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["companyInfo"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/companyInfo",
      "title" => "{$this->params['domainName']} » ".$this->translate("Company Info"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("General"),
            "items" => [
              [
                "title" => $this->translate("Slogan"),
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
                "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg."),
              ],
            ],
          ],

          [
            "title" => $this->translate("Headquarter (Invoicing)"),
            "items" => [
              [
                "title" => $this->translate("Company name"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCompanyName",
                  "value" => $settings['headquarterCompanyName'],
                ]),
              ],
              [
                "title" => $this->translate("Street, 1st line"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterStreet1",
                  "value" => $settings['headquarterStreet1'],
                ]),
              ],
              [
                "title" => $this->translate("Street, 2st line"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterStreet2",
                  "value" => $settings['headquarterStreet2'],
                ]),
              ],
              [
                "title" => $this->translate("ZIP"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterZIP",
                  "value" => $settings['headquarterZIP'],
                ]),
              ],
              [
                "title" => $this->translate("City"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCity",
                  "value" => $settings['headquarterCity'],
                ]),
              ],
              [
                "title" => $this->translate("Region"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterRegion",
                  "value" => $settings['headquarterRegion'],
                ]),
              ],
              [
                "title" => $this->translate("Country"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCountry",
                  "value" => $settings['headquarterCountry'],
                ]),
              ],
              [
                "title" => $this->translate("Company ID"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterCompanyID",
                  "value" => $settings['headquarterCompanyID'],
                ]),
              ],
              [
                "title" => $this->translate("Tax ID"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterTaxID",
                  "value" => $settings['headquarterTaxID'],
                ]),
              ],
              [
                "title" => $this->translate("VAT ID"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_headquarterVatID",
                  "value" => $settings['headquarterVatID'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Contact information"),
            "items" => [
              [
                "title" => $this->translate("Contact phone number"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactPhoneNumber",
                  "value" => $settings['contactPhoneNumber'],
                ]),
              ],
              [
                "title" => $this->translate("Contact email"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactEmail",
                  "value" => $settings['contactEmail'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Social networks"),
            "items" => [
              [
                "title" => $this->translate("Facebook page URL"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlFacebook",
                  "value" => $settings['urlFacebook'],
                ]),
              ],
              [
                "title" => $this->translate("Twitter URL"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlTwitter",
                  "value" => $settings['urlTwitter'],
                ]),
              ],
              [
                "title" => $this->translate("Instagram profile URL"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_urlInstagram",
                  "value" => $settings['urlInstagram'],
                ]),
              ],
              [
                "title" => $this->translate("YouTube channel URL"),
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