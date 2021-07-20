<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table;

class Delete extends \ADIOS\Core\Action {

  public function render() {
    try {
      $tmpModel = $this->adios->getModel($this->params['model']);

      if (is_numeric($this->params['id'])) {
        $tmpModel->deleteRow($this->params['id']);
      } else {
        throw new \ADIOS\Core\ActionException("Nothing to delete.");
      }

      return "1";
    } catch (\ADIOS\Core\ActionException $e) {
      return $e->getMessage();
    }
  }
}