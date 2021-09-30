<?php

namespace ADIOS\Actions\Settings;

class Miscellaneous extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["miscellaneous"];
    $locale = $this->adios->locale->getAll();

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "miscellaneous",
      "title" => "Miscellaneous settings",
      "template" => [
        "tabs" => [
          [
            "title" => "Locale",
            "items" => [
              [
                "title" => "Currency symbol",
                "description" => "Symbol of the currency displayed in the administration panel.",
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_localeCurrencySymbol",
                  "value" => $settings['localeCurrencySymbol'] ?: $locale["currencySymbol"],
                ]),
              ],
              [
                "title" => "Date format",
                "description" =>
                  "Date format to be used in the administration panel."."<br/>".
                  "Use notation compatible with PHP's date() function."."<br/>".
                  "List of avaialable options is <a href='https://www.php.net/manual/en/datetime.format.php' target=_blank>here 🡕</a>."
                ,
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_localeDateFormat",
                  "value" => $settings['localeDateFormat'] ?: $locale["dateFormat"],
                ]),
              ],
              [
                "title" => "Time format",
                "description" =>
                  "Time format to be used in the administration panel."."<br/>".
                  "Same rules as for date format apply."."<br/>"
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
            "title" => "Shopping cart",
            "items" => [
              [
                "title" => "Days to abandon shopping cart",
                "description" => "Number of days after which the shopping cart will be treated as abanoned.",
                "input" => $this->adios->ui->Input([
                  "type" => "int",
                  "uid" => "{$this->uid}_shoppingCartDaysToAbandon",
                  "value" => $settings['shoppingCartDaysToAbandon'],
                  "unit" => "day(s)",
                ]),
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}