<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI;

class FileBrowser extends \ADIOS\Core\Action {
  public function render() {
    return $this->adios->ui->FileBrowser($this->params)->render();
  }
}