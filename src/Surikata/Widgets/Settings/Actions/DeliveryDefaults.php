<?php

namespace ADIOS\Actions\Settings;

class DeliveryDefaults extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["delivery"]["defaults"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "delivery/defaults",
      "title" => "Delivery defaults",
      "template" => [
        "items" => [
          [
            "title" => "Delivery day",
            "input" => $this->adios->ui->Input([
              "type" => "int",
              "uid" => "{$this->uid}_deliveryDay",
              "value" => $settings['deliveryDay'],
              "enum_values" => [
                0 => "Same day of the order",
                1 => "Next day after the order",
                2 => "2 days after the order",
                3 => "3 days after the order",
                4 => "4 days after the order",
                5 => "5 days after the order",
                6 => "6 days after the order",
                7 => "7 days after the order",
                8 => "8 days after the order",
                9 => "9 days after the order",
                10 => "10 days after the order",
                11 => "11 days after the order",
                12 => "12 days after the order",
                13 => "13 days after the order",
                14 => "14 days after the order",
              ],
            ]),
          ],
          [
            "title" => "Delivery time",
            "input" => $this->adios->ui->Input([
              "type" => "time",
              "uid" => "{$this->uid}_deliveryTime",
              "value" => $settings['deliveryTime'],
            ]),
          ],
          [
            "title" => "Order deadline",
            "input" => $this->adios->ui->Input([
              "type" => "time",
              "uid" => "{$this->uid}_orderDeadline",
              "value" => $settings['orderDeadline'],
            ]),
          ],
        ],
      ],
    ]);
  }
}