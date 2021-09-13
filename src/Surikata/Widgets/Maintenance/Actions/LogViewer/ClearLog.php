<?php

namespace ADIOS\Actions\Maintenance\LogViewer;

class ClearLog extends \ADIOS\Core\Action {
  public function render() {
    $logSeverity = $this->params['severity'];
    if (!in_array($logSeverity, ["info", "warning", "error"])) {
      $logSeverity = "info";
    }

    $logFile = "{$this->adios->config['log_dir']}/".date("Y")."/".date("m")."/".date("d")."/{$logSeverity}.log";
    if (is_file($logFile)) {
      unlink($logFile);
    }

    return [TRUE];
  }
}