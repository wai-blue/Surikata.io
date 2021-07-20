<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Tree;
class GetItemText extends \ADIOS\Core\Action {
  public function render() {
    $model = $this->adios->getModel($this->params['model']);

    $tmp = reset($model
      ->selectRaw($model->getFullTableSQLName().".id")
      ->selectRaw("(".str_replace("{%TABLE%}", $model->getFullTableSQLName(), $model->lookupSqlValue).") as ___lookupSqlValue")
      ->where('id', (int) $this->params['id'])
      ->get()
      ->toArray()
    );

    return $tmp['___lookupSqlValue'];
  }
}