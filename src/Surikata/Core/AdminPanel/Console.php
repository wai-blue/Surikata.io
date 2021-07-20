<?php

namespace Surikata\Core\AdminPanel;

class Console extends \ADIOS\Core\Console {
  public function log($header, $message) {
    if (!is_dir(LOG_DIR)) {
      mkdir(LOG_DIR, "0777");
    }

    $h = fopen(LOG_DIR."/".date("Y-m-d").".log", "a");
    fwrite($h, date("Y-m-d H:i:s")."\t{$header}\t{$message}\n");
    fclose($h);
  }

  public function clear() {
  }

  public function getLogs() {
    return [];
  }

  public function getContents() {
    return "";
  }

}