<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/


namespace ADIOS\Core;

/**
 * Debugger console for ADIOS application.
 */
class Console {
  public function __construct(&$adios) {
    $this->adios = $adios;
  }
  
  /**
   * Logs a message to the console
   *
   * @param  string $header Context of the message
   * @param  string $message Message to be logged
   * @return void
   */
  public function log($header, $message) {
    $_SESSION[_ADIOS_ID]['console'][] = [
      'header' => trim(date('H:i:s')." ".$header),
      'message' => trim($message),
    ];

    if (!empty($this->adios->config['console']['log_file'])) {
      $logFile = $this->adios->config['console']['log_file'];

      $h = fopen($logFile, "a");
      fwrite($h, date("Y-m-d H:i:s")."\t{$header}\t{$message}\n");
      fclose($h);
    }
  }
  
  /**
   * Clears the console
   *
   * @return void
   */
  public function clear() {
    $_SESSION[_ADIOS_ID]['console'] = [];
  }
  
  /**
   * Returns list of logged messages
   *
   * @return array List of logged messages. Empty array in case of no messages.
   */
  public function getLogs() {
    return $_SESSION[_ADIOS_ID]['console'] ?? [];
  }
  
  /**
   * Returns string-formatted content of the console
   *
   * @return string String-formatted content of the console
   */
  public function getContents() {
    $contents = "";
    foreach ($this->getLogs() as $log) {
      $contents .= "{$log['header']}\n{$log['message']}\n\n";
    }
    return $contents;
  }

}