<?php

namespace ADIOS\Actions\Website;

class Design extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domain']]["design"];

    $themeEnumValues = [
      "" => "Choose your theme",
    ];
    if (is_dir($this->adios->config['themes_dir'])) {
      foreach (scandir($this->adios->config['themes_dir']) as $file) {
        if (in_array($file, [".", ".."])) continue;
        $themeEnumValues[$file] = $file;
      }
    }

    $ponukyMenuEnumValues = $this->adios->getModel("Widgets/Website/Models/WebMenu")->getEnumValues();

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domain']}/design",
      "title" => "Website - {$this->params['domain']} - Design",
      "template" => [
        "tabs" => [
          [
            "title" => "Theme",
            "items" => [
              [
                "title" => "Choose theme",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_theme",
                  "value" => $settings['theme'],
                  "enum_values" => $themeEnumValues,
                ]),
              ],
            ],
          ],

          [
            "title" => "Colors: Main body background",
            "items" => [
              [
                "title" => "Telo stránky - Farba pozadia",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyBgColor",
                  "value" => $settings['bodyBgColor'],
                ]),
                "description" => "Niektoré šablóny nemusia podporovať voliteľnosť farieb.",
              ],
              [
                "title" => "Colors: Main body regular text",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyTextColor",
                  "value" => $settings['bodyTextColor'],
                ]),
              ],
              [
                "title" => "Colors: Main body hyperlinks",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyLinkColor",
                  "value" => $settings['bodyLinkColor'],
                ]),
              ],
              [
                "title" => "Colors: Main body headings",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyHeadingColor",
                  "value" => $settings['bodyHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Colors: Header",
            "items" => [
              [
                "title" => "Colors: Header background",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerBgColor",
                  "value" => $settings['headerBgColor'],
                ]),
                "description" => "Niektoré šablóny nemusia podporovať voliteľnosť farieb.",
              ],
              [
                "title" => "Colors: Header regular text",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerTextColor",
                  "value" => $settings['headerTextColor'],
                ]),
              ],
              [
                "title" => "Colors: Header hyperlinks",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerLinkColor",
                  "value" => $settings['headerLinkColor'],
                ]),
              ],
              [
                "title" => "Colors: Header headings",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerHeadingColor",
                  "value" => $settings['headerHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Colors: Footer",
            "items" => [
              [
                "title" => "Colors: Footer background",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerBgColor",
                  "value" => $settings['footerBgColor'],
                ]),
                "description" => "Niektoré šablóny nemusia podporovať voliteľnosť farieb.",
              ],
              [
                "title" => "Colors: Footer regular text",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerTextColor",
                  "value" => $settings['footerTextColor'],
                ]),
              ],
              [
                "title" => "Colors: Footer hyperlinks",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerLinkColor",
                  "value" => $settings['footerLinkColor'],
                ]),
              ],
              [
                "title" => "Colors: Footer headings",
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerHeadingColor",
                  "value" => $settings['footerHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => "Custom CSS",
            "items" => [
              [
                "title" => "Custom CSS statements",
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_custom_css",
                  "value" => $settings['custom_css'],
                ]),
                "description" => "Only for experienced users. CSS knowledge is required. Supported CSS statements may vary in different templates.",
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}