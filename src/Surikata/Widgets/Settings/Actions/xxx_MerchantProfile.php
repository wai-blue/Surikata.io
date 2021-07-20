<?php

namespace ADIOS\Actions\Settings;

class MerchantProfile extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"]["profile"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/profile",
      "title" => "e-Shop profile",
      "template" => [
        "tabs" => [
          [
            "title" => "Merchant profile",
            "items" => [
              [
                "title" => "Name of the store",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_storeName",
                  "value" => $settings['storeName'],
                ]),
              ],
              [
                "title" => "A brief slogan",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_slogan",
                  "value" => $settings['slogan'],
                ]),
                "description" => "It can be displayed e.g. in the footer of the web.",
              ],
              [
                "title" => "Web Domain",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_domena",
                  "value" => $settings['rootUrl'],
                ]),
                "description" => "Without 'http://'. Example: www.my-eshop.com or my-eshop.com",
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
            "title" => "Main Address",
            "items" => [
              [
                "title" => "Street, 1st line",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sidlo_ulica_1",
                  "value" => $settings['sidlo_ulica_1'],
                ]),
              ],
              [
                "title" => "Street, 2st line",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sidlo_ulica_2",
                  "value" => $settings['sidlo_ulica_2'],
                ]),
              ],
              [
                "title" => "ZIP",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sidlo_psc",
                  "value" => $settings['sidlo_psc'],
                ]),
              ],
              [
                "title" => "City",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_sidlo_mesto",
                  "value" => $settings['sidlo_mesto'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Contact",
            "items" => [
              [
                "title" => "Contact email",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactEmail",
                  "value" => $settings['contactEmail'],
                  "pattern" => "[abcdefghijklmnopqrstuvwxyz0123456789._%\+\-]+@[abcdefghijklmnopqrstuvwxyz0123456789.\-]+\.[abcdefghijklmnopqrstuvwxyz]{2,4}",
                ]),
              ],
              [
                "title" => "Contact phone number",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_contactPhoneNumber",
                  "value" => $settings['contactPhoneNumber'],
                  "pattern" => '\+\d{3} \d{3} \d{3} \d{3}',
                ]),
                "description" => "Format: +421 123 456 789",
              ],
            ],
          ],

          [
            "title" => "Social networks",
            "items" => [
              [
                "title" => "Facebook",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_url_facebook",
                  "value" => $settings['url_facebook'],
                ]),
              ],
              [
                "title" => "Twitter",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_url_twitter",
                  "value" => $settings['url_twitter'],
                ]),
              ],
              [
                "title" => "Youtube",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_url_youtube",
                  "value" => $settings['url_youtube'],
                ]),
              ],
              [
                "title" => "Instagram",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_url_instagram",
                  "value" => $settings['url_instagram'],
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}