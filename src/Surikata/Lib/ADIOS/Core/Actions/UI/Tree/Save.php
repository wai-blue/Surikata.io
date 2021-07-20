<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Tree;

class Save extends \ADIOS\Core\Action {
  public function render() {
    $params = $this->params;

    $model = $this->adios->getModel($params['model']);

    // najdem stlpec pre rodica

    foreach ($model->columns() as $colName => $colDef) {
      if ($colDef["type"] == "lookup" && $colDef["model"] == $model->name) {
        $parentColumn = $colName;
        $orderColumn = $colDef["order_column"];
      }
    }

    $values = @json_decode($params['values'], TRUE);

    $order = 0;

    foreach ($values as $value) {
      if ($value['toDelete']) {
        foreach ($model->findForeignKeyModels() as $fkModelName => $fkColumn) {
          $this->adios->getModel($fkModelName)->where($fkColumn, $value['id'])->update([
            $fkColumn => NULL
          ]);
        }

        $model->where('id', $value['id'])->delete();
      } else {
        $updateData = [
          $parentColumn => ($value['parent'] ? $value['parent'] : NULL),
        ];
        
        if (!empty($orderColumn)) {
          $updateData[$orderColumn] = $order++;
        }

        $model->where('id', $value['id'])->update($updateData);
      }
    }

    echo "1";
  }
}