<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\SettingsPanel;

class Save extends \ADIOS\Core\Action {
  public function render() {
    if (empty($this->params['values'])) {
      return "";
    }
    if (empty($this->params['__settings_group'])) {
      return "";
    }

    $values = @json_decode($this->params['values'], TRUE);

    $settings = [];
    
    if (is_array($values)) {
      foreach ($values as $key => $value) {
        $settings[$key] = $value;
      }
    }

    $this->adios->saveConfig([
      'settings' => [
        $this->params['__settings_group'] => $settings
      ]
    ]);

  }
}