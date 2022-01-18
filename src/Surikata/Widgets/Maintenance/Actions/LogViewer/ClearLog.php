<?php

namespace ADIOS\Actions\Maintenance\LogViewer;

class ClearLog extends \ADIOS\Core\Widget\Action {
  public function render() {
    $logSeverity = $this->params['severity'];

    $this->adios->clearLog("core", $logSeverity);

    return [TRUE];
  }
}