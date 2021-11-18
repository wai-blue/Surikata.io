<?php

namespace ADIOS\Actions\Website;

class Design extends \ADIOS\Core\Widget\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["web"][$this->params['domainName']]["design"];

    $themeEnumValues = [
      "" => $this->translate("Choose your theme"),
    ];
    foreach ($this->adios->websiteRenderer->themeFolders as $themeFolder) {
      foreach (scandir($themeFolder) as $file) {
        if (strpos(".", $file) !== FALSE) continue;
        $themeEnumValues[$file] = $file;
      }
    }

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "web/{$this->params['domainName']}/design",
      "title" => "{$this->params['domainName']} » ".$this->translate("Design"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("Theme"),
            "items" => [
              [
                "title" => $this->translate("Choose theme"),
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
            "title" => $this->translate("Colors: ").$this->translate("Main theme colors"),
            "items" => [
              [
                "title" => $this->translate("Main color"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_themeMainColor",
                  "value" => $settings['themeMainColor'],
                ]),
                "description" => $this->translate("Some themes may not support coloring."),
              ],
              [
                "title" => $this->translate("Second color"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_themeSecondColor",
                  "value" => $settings['themeSecondColor'],
                ]),
              ],
              [
                "title" => $this->translate("Third color"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_themeThirdColor",
                  "value" => $settings['themeThirdColor'],
                ]),
              ],
              [
                "title" => $this->translate("Grey color"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_themeGreyColor",
                  "value" => $settings['themeGreyColor'],
                ]),
              ],
              [
                "title" => $this->translate("Light grey color"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_themeLightGreyColor",
                  "value" => $settings['themeLightGreyColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Colors: ").$this->translate("Main body background"),
            "items" => [
              [
                "title" => $this->translate("Colors: ").$this->translate("Main body background"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyBgColor",
                  "value" => $settings['bodyBgColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Main body regular text"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyTextColor",
                  "value" => $settings['bodyTextColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Main body hyperlinks"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyLinkColor",
                  "value" => $settings['bodyLinkColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Main body headings"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_bodyHeadingColor",
                  "value" => $settings['bodyHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Colors: ").$this->translate("Header"),
            "items" => [
              [
                "title" => $this->translate("Colors: ").$this->translate("Header background"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerBgColor",
                  "value" => $settings['headerBgColor'],
                ]),
                "description" => $this->translate("Some templates may not support color selectivity."),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Header regular text"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerTextColor",
                  "value" => $settings['headerTextColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Header hyperlinks"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerLinkColor",
                  "value" => $settings['headerLinkColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Header headings"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_headerHeadingColor",
                  "value" => $settings['headerHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Colors: ").$this->translate("Footer"),
            "items" => [
              [
                "title" => $this->translate("Colors: ").$this->translate("Footer background"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerBgColor",
                  "value" => $settings['footerBgColor'],
                ]),
                "description" => $this->translate("Some particles may not support color selectivity."),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Footer regular text"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerTextColor",
                  "value" => $settings['footerTextColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Footer hyperlinks"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerLinkColor",
                  "value" => $settings['footerLinkColor'],
                ]),
              ],
              [
                "title" => $this->translate("Colors: ").$this->translate("Footer headings"),
                "input" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_footerHeadingColor",
                  "value" => $settings['footerHeadingColor'],
                ]),
              ],
            ],
          ],

          [
            "title" => $this->translate("Custom CSS"),
            "items" => [
              [
                "title" => $this->translate("Custom CSS statements"),
                "input" => $this->adios->ui->Input([
                  "type" => "text",
                  "uid" => "{$this->uid}_custom_css",
                  "value" => $settings['custom_css'],
                ]),
                "description" => $this->translate("Only for experienced users. CSS knowledge is required. Supported CSS statements may vary in different templates."),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}