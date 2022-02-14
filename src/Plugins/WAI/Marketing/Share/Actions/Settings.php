<?php

namespace ADIOS\Actions\Plugins\WAI\Marketing\Share;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Marketing/Share");

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Marketing/Share",
      "template" => [
        "tabs" => [
          [
            "title" => "Facebook",
            "items" => [
              [
                "title" => $this->translate("Facebook Insights app ID"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_FacebookMetaTagAppId",
                  "value" => $settings['FacebookMetaTagAppId'],
                ]),
                "description" => $this->translate("In order to use Facebook Insights you must add the Facebook app ID. Insights lets you view analytics for traffic to your site from Facebook.")
              ],
              [
                "title" => $this->translate("Enable sharing"),
                "input" => $this->adios->ui->Input([
                  "type" => "bool",
                  "uid" => "{$this->uid}_FacebookEnableSharing",
                  "value" => $settings['FacebookEnableSharing'],
                ])
              ],
            ],
          ],
          [
            "title" => "Twitter",
            "items" => [
              [
                "title" => $this->translate("Enable sharing"),
                "input" => $this->adios->ui->Input([
                  "type" => "bool",
                  "uid" => "{$this->uid}_TwitterEnableSharing",
                  "value" => $settings['TwitterEnableSharing'],
                ])
              ],
            ],
          ]
        ],
      ],
    ]);
  }
}