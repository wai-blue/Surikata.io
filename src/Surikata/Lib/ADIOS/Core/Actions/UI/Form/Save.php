<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Form;

class Save extends \ADIOS\Core\Action {
  public function render($params = []) {
    $saveParams = json_decode($params['values'], TRUE);
    $saveParams['id'] = $params['id'];

    return $this->adios->getModel($params['model'])->formSave($saveParams);

  }
}