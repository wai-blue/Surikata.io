<?php

namespace ADIOS\Actions\Website;

class Profile extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domain']]["profile"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domain']}/profile",
      "title" => "Website - {$this->params['domain']} - Profile",
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