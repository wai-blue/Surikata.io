<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table\Import\CSV;

/**
 * @package UI\Actions
 */
class Preview extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function render() {
    $model = $this->adios->getModel($this->params['model']);
    
    $csv = file_get_contents("{$this->adios->config['files_dir']}/csv-import/{$this->params['csvFile']}");
    var_dump($model->columns());

    return "<pre>{$csv}</pre>";

  }
}
