<?php

namespace ADIOS\Actions\Plugins\WAI\Misc\ContactForm;

class Settings extends \ADIOS\Core\Plugin\Action {
  public function render() {
    $settings = $this->adios->getPluginSettings("WAI/Misc/ContactForm");

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Misc/ContactForm",
      "title" => $this->translate("Contact form - Settings"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("Email settings"),
            "items" => [
              [
                "title" => $this->translate("Send email"),
                "input" => $this->adios->ui->Input([
                  "type" => "boolean",
                  "uid" => "{$this->uid}_sendMailIsEnabled",
                  "value" => $settings['sendMailIsEnabled'],
                ]),
                "description" => $this->translate("An email will be sent once the form has been completed.")
              ],
              [
                "title" => $this->translate("Recipient email"),
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_recipientEmail",
                  "value" => $settings['recipientEmail'],
                ]),
                "description" => $this->translate("Email where the email about the completed form will come")
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}