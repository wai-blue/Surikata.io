<?php

namespace ADIOS\Actions\Website;

class LegalDisclaimers extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["legalDisclaimers"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/legalDisclaimers",
      "title" => "{$this->params['domainName']} » Legal disclaimers",
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("General terms and conditions"),
            "items" => [
              [
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "interface" => "formatted_text",
                  "uid" => "{$this->uid}_generalTerms",
                  "value" => $settings['generalTerms'],
                ]),
                "description" => $this->translate("General terms text. Can be used as a content of the website."),
              ],
              [
                "input" => $this->adios->ui->Input([
                  "type" => "file",
                  "uid" => "{$this->uid}_generalTermsPDF",
                  "value" => $settings['generalTermsPDF'],
                ]),
                "description" => $this->translate("PDF version of general terms, if available. Can be used as an attachment in email."),
              ],
            ],
          ],

          [
            "title" => $this->translate("Privacy policy"),
            "items" => [
              [
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "interface" => "formatted_text",
                  "uid" => "{$this->uid}_privacyPolicy",
                  "value" => $settings['privacyPolicy'],
                ]),
                "description" => $this->translate("Privacy policy text. Can be used as a content of the website."),
              ],
              [
                "input" => $this->adios->ui->Input([
                  "type" => "file",
                  "uid" => "{$this->uid}_privacyPolicyPDF",
                  "value" => $settings['privacyPolicyPDF'],
                ]),
                "description" => $this->translate("PDF version of privacy policy, if available. Can be used as an attachment in email."),
              ],
            ],
          ],

          [
            "title" => "Return policy",
            "items" => [
              [
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "interface" => "formatted_text",
                  "uid" => "{$this->uid}_returnPolicy",
                  "value" => $settings['returnPolicy'],
                ]),
                "description" => $this->translate("Return policy text. Can be used as a content of the website."),
              ],
              [
                "input" => $this->adios->ui->Input([
                  "type" => "file",
                  "uid" => "{$this->uid}_returnPolicyPDF",
                  "value" => $settings['returnPolicyPDF'],
                ]),
                "description" => $this->translate("PDF version of return policy, if available. Can be used as an attachment in email."),
              ],
            ],
          ],


        ],
      ],
    ]);
  }
}