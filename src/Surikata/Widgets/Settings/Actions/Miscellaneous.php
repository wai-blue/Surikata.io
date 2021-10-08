<?php

namespace ADIOS\Actions\Settings;

class Miscellaneous extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["miscellaneous"];
    $locale = $this->adios->locale->getAll();

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "miscellaneous",
      "title" => $this->translate("Miscellaneous settings"),
      "template" => [
        "tabs" => [
          [
            "title" => $this->translate("Locale"),
            "items" => [
              [
                "title" => $this->translate("Currency symbol"),
                "description" => $this->translate("Symbol of the currency displayed in the administration panel."),
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_localeCurrencySymbol",
                  "value" => $settings['localeCurrencySymbol'] ?: $locale["currencySymbol"],
                ]),
              ],
              [
                "title" => $this->translate("Date format"),
                "description" =>
                  $this->translate("Date format to be used in the administration panel.")."<br/>".
                  $this->translate("Use notation compatible with PHP's date() function.")."<br/>".
                  $this->translate("List of available options is"). " <a href='https://www.php.net/manual/en/datetime.format.php' target=_blank>here 🡕</a>."
                ,
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_localeDateFormat",
                  "value" => $settings['localeDateFormat'] ?: $locale["dateFormat"],
                ]),
              ],
              [
                "title" => $this->translate("Time format"),
                "description" =>
                  $this->translate("Time format to be used in the administration panel.")."<br/>".
                  $this->translate("Same rules as for date format apply.")."<br/>"
                ,
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_localeTimeFormat",
                  "value" => $settings['localeTimeFormat'] ?: $locale["timeFormat"],
                ]),
              ],
            ],
          ],
          [
            "title" => $this->translate("Shopping cart"),
            "items" => [
              [
                "title" => $this->translate("Days to abandon shopping cart"),
                "description" => $this->translate("Number of days after which the shopping cart will be treated as abanoned."),
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_shoppingCartDaysToAbandon",
                  "value" => $settings['shoppingCartDaysToAbandon'],
                  "unit" => $this->translate("day(s)"),
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}