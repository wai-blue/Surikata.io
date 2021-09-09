<?php

namespace ADIOS\Actions\Settings;

class LogViewer extends \ADIOS\Core\Action {
  public function preRender() {
    $logSeverity = $this->params['severity'];
    if (!in_array($logSeverity, ["info", "warning", "error"])) {
      $logSeverity = "info";
    }

    $logFile = "{$this->adios->config['log_dir']}/".date("Y")."/".date("m")."/".date("d")."/{$logSeverity}.log";
    if (is_file($logFile)) {
      $logLines = file($logFile);
    } else {
      $logLines = [];
    }
    return [
      "severity" => $logSeverity,
      "logLines" => array_reverse($logLines),
    ];
  }
}