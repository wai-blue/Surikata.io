<?php

namespace ADIOS\Actions\Plugins\WAI\Product\Catalog;

class Settings extends \ADIOS\Core\Action {
  public function render() {
    $settings = $this->adios->config["settings"]["plugins"]["WAI/Product/Catalog"];

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Product/Catalog",
      "title" => "WAI/Product/Catalog - Settings",
      "template" => [
        "tabs" => [
          [
            "title" => "Basic settings",
            "items" => [
              [
                "title" => "Use AJAX loading",
                "input" => $this->adios->ui->Input([
                  "type" => "boolean",
                  "uid" => "{$this->uid}_use_ajax_loading",
                  "value" => $settings['use_ajax_loading'],
                ]),
                // "description" => "Bude použité, ak aktívna stránka nebude mať nastavenú vlastnú hodnotu.",
              ],
              [
                "title" => "Text on button for load products",
                "input" => $this->adios->ui->Input([
                  "type" => "varchar",
                  "uid" => "{$this->uid}_btn_load_products_text",
                  "value" => $settings['btn_load_products_text'],
                ]),
                // "description" => "Bude použité, ak aktívna stránka nebude mať nastavenú vlastnú hodnotu.",
              ],
            ],
          ],
        ],
      ],
    ]);
  }
}