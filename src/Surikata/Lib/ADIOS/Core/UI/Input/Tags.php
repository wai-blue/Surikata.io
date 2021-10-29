<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class Tags extends \ADIOS\Core\Input {
  public function render() {
    $model = $this->adios->getModel($this->params['model']);

    $html = print_r($this->params, TRUE);

    return $html;
  }
}
