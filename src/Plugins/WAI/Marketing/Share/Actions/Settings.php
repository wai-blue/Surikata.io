<?php

namespace ADIOS\Actions\Plugins\WAI\Marketing\Share;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Marketing/Share");

    return $this->adios->renderAction("UI/SettingsPanel", [
      "template" => [
        "tabs" => [
          [
            "title" => "Facebook",
            "items" => [
              [
                "title" => "Facebook share app id",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_facebook_meta_app_id",
                  "value" => $settings['facebook_meta_app_id'],
                ]),
              ],
            ],
          ],
          [
            "title" => "Twitter",
            "items" => [
              [
                "title" => "Twitter share app id",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_twitter_meta_app_id",
                  "value" => $settings['twitter_meta_app_id'],
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}