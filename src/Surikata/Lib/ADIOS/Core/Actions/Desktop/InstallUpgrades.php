<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\Desktop;

class InstallUpgrades extends \ADIOS\Core\Action {
  function render() {
    $html = "
      <div class='card shadow mb-4'>
        <div class='card-header py-3'>
          <h6 class='m-0 font-weight-bold text-primary'>Installing upgrades</h6>
        </div>
        <div class='card-body'>
    ";
    foreach ($this->adios->models as $modelName) {
      $model = $this->adios->getModel($modelName);
      if ($model->hasAvailableUpgrades()) {
        $html .= "{$model->name}: ";
        try {
          $model->installUpgrades();
          $html .= "<span style='color:green'>OK</span><br/>";
        } catch (\ADIOS\Core\DBException $e) {
          $html .= "<span style='color:red'>".$e->getMessage()."</span><br/>";
        }
      }
    }
    $html .= "
        </div>
      </div>

    ";

    return $html;
  }
}