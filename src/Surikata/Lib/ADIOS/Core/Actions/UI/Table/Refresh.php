<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table;

class Refresh extends \ADIOS\Core\Action {
  public function render($params = []) {
    $tmp['refresh'] = 1;
    $tmp['uid'] = $_REQUEST['uid'];
    $this->adios->setUid($tmp['uid']);
    $table = $this->adios->ui->Table($tmp);
    return $table->render();
  }
}