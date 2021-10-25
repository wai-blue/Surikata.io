<?php

namespace ADIOS\Actions\Maintenance;

class LogViewer extends \ADIOS\Core\Action {
  public function preRender() {
    $logSeverity = $this->params['severity'];
    if (!in_array($logSeverity, ["info", "warning", "error"])) {
      $logSeverity = $this->translate("info");
    }

    $logFile = "{$this->adios->config['log_dir']}/".date("Y")."/".date("m")."/".date("d")."/{$logSeverity}.log";
    if (is_file($logFile)) {
      $logLines = file($logFile);
    } else {
      $logLines = [];
    }
    return [
      "severity" => $this->translate($logSeverity),
      "logLines" => array_reverse($logLines),
    ];
  }
}