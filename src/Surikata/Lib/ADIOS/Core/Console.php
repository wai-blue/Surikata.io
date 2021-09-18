<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/


namespace ADIOS\Core;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

/**
 * Debugger console for ADIOS application.
 */
class Console {
  var $logger = NULL;

  var $infos = [];
  var $warnings = [];
  var $errors = [];

  public function __construct(&$adios) {
    $this->adios = $adios;

    // inicializacia loggerov
    $this->logger = new Logger('core');
    $infoStreamHandler = new RotatingFileHandler($this->adios->config['log_dir'].'/info.log', 1000, Logger::INFO);
    $infoStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $warningStreamHandler = new RotatingFileHandler($this->adios->config['log_dir'].'/warning.log', 1000, Logger::WARNING);
    $warningStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $errorStreamHandler = new RotatingFileHandler($this->adios->config['log_dir'].'/error.log', 1000, Logger::ERROR);
    $errorStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $this->logger->pushHandler($infoStreamHandler);
    $this->logger->pushHandler($warningStreamHandler);
    $this->logger->pushHandler($errorStreamHandler);

  }
  
  /**
   * Logs a message to the console
   *
   * @param  string $message Message to be logged
   * @return void
   */
  public function log($message) {
    $_SESSION[_ADIOS_ID]['console'][] = [
      'header' => date('H:i:s'),
      'message' => trim($message),
    ];

    // if (!empty($this->adios->config['console']['log_file'])) {
    //   $logFile = $this->adios->config['console']['log_file'];

    //   $h = fopen($logFile, "a");
    //   fwrite($h, date("Y-m-d H:i:s")."\t{$message}\n");
    //   fclose($h);
    // }
  }

  public function info($message, array $context = []) {
    $this->logger->info($message, $context);
    $this->infos[microtime()] = [$message, $context];
  }
  
  public function warning($message, array $context = []) {
    $this->logger->warning($message, $context);
    $this->warnings[microtime()] = [$message, $context];
  }
  
  public function error($message, array $context = []) {
    $this->logger->error($message, $context);
    $this->log($message);
    $this->errors[microtime()] = [$message, $context];
  }

  public function getInfos() {
    return $this->infos;
  }
  
  public function getWarnings() {
    return $this->warnings;
  }
  
  public function getErrors() {
    return $this->errors;
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

  public function convertLogsToHtml($logs, $addTimestamps = FALSE) {
    $html = "";
    foreach ($logs as $mictotime => $log) {
      if ($addTimestamps) {
        list($msec, $sec) = explode(" ", $mictotime);
        $html .= date("Y-m-h H:i:s", $sec).".".round($msec*1000)." ";
      }
      $html .= hsc($log[0])." ".hsc($log[1]['exception'])."<br/>";
    }
    return $html;
  }

}